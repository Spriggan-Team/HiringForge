<?php

namespace App\Infrastructure\Persistence\Commands;

use App\Domain\Shared\Service\VectorServiceInterface;
use App\Infrastructure\Persistence\Service\SkillMatcherService;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillAliasEntity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\String\Slugger\SluggerInterface;



#[AsCommand(name: 'app:seed-skills', description: 'Import skills ESCO et O*NET into bdd with semactics validation ')]
class ImportSkillsCommand extends Command
{
    public function __construct(
        private ManagerRegistry $doctrine,
        private EntityManagerInterface $em,
        private SluggerInterface $slugger,
        private SkillMatcherService $matcher,
        private VectorServiceInterface $vectorService,
    ) {
        parent::__construct(); 
    }

    protected function configure(): void
    {
        $this->addOption('source', 's', InputOption::VALUE_OPTIONAL, 'Source à importer (esco ou onet)', 'esco');
        $this->addOption('vector-search', 'vs', InputOption::VALUE_OPTIONAL, 'Define similary detection activation', "false");
    }
    

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //--------------------------------
        // Configuration
        //--------------------------------

        // 1. Disable SQL Logging
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        // 2. Disable Symfony Stopwatch / DBAL Debug Middleware

        $config = $this->em->getConnection()->getConfiguration();
        if (method_exists($config, 'getMiddlewares') && method_exists($config, 'setMiddlewares')) {
            $middlewares = array_filter(
                $config->getMiddlewares(),
                fn($middleware) => !($middleware instanceof \Symfony\Bridge\Doctrine\Middleware\Debug\Middleware)
            );
            $config->setMiddlewares($middlewares);
        }

        $source = $input->getOption('source');
        $enableVectorSearch = filter_var(
            $input->getOption('vector-search'),
            FILTER_VALIDATE_BOOLEAN
        );

        $config = match ($source) {
            'esco' => [
                'paths' => [
                    __DIR__ . "/../../../data/csv/esco/skills_fr.csv",
                ],
                'mapping' => [
                    'name'          => 5,
                    'altLabels'     => 6,
                    'external_code' => 2,
                    'canonicalName' => 5,
                ],
                'locale'    => 'fr',
                'delimiter' => ',',
            ],
            'onet' => [
                'paths' => [
                    __DIR__ . "/../../../data/csv/onet/onet_software_skills.csv",
                ],
                'mapping' => [
                    'name'          => 3,
                    'altLabels'     => 5,
                    'external_code' => 1,
                    'canonicalName' => 3,
                ],
                'locale'    => 'en',
                'delimiter' => ',',
            ],
            default => null,
        };

        if (!$config) {
            $output->writeln("<error>Unknown source '$source'. Use 'esco' or 'onet'.</error>");
            return Command::INVALID;
        }

        $locale = $config['locale'] ?? 'fr';

        $getVal = function (array $row, ?int $excelColumn): string {
            if ($excelColumn === null || $excelColumn < 1) {
                return '';
            }
            $phpIndex = $excelColumn - 1;
            return isset($row[$phpIndex]) ? trim(mb_convert_encoding($row[$phpIndex], 'UTF-8', 'UTF-8')) : '';
        };

        $totalImported = 0;
        $batchSize = 200;
        $failedEntityCount = 0;

        //--------------------------------
        // Pre-loading DB Caches
        //--------------------------------

        $output->writeln("<info>Pre-loading database cache to prevent memory leaks...</info>");

        $processedAliases = [];
        $rawAliases = $this->em->getConnection()
            ->fetchAllAssociative('SELECT LOWER(alias) as alias FROM skill_aliases');

        foreach ($rawAliases as $row) {
            $processedAliases[md5($row['alias'])] = true;
        }
        unset($rawAliases);

        $processedSlugs = [];
        $rawSlugs = $this->em->getConnection()
            ->fetchAllAssociative('SELECT slug FROM skills_translation');
            
        foreach ($rawSlugs as $row) {
            $processedSlugs[md5($row['slug'])] = true;
        }
        unset($rawSlugs);

        $processedCodes = [];
        $codeColumn = ($source === 'esco') ? 'esco_uri' : 'onet_code';

        $rawCodes = $this->em->getConnection()
            ->fetchAllAssociative("SELECT $codeColumn as code FROM skills WHERE $codeColumn IS NOT NULL");

        foreach ($rawCodes as $row) {
            $processedCodes[md5($row['code'])] = true;
        }
        unset($rawCodes);

        //--------------------------------
        // SEEDINGS
        //--------------------------------

        foreach ($config['paths'] as $filePath) {
            if (!file_exists($filePath)) {
                $output->writeln("<error>File not found: $filePath</error>");
                continue;
            }

            if (($handle = fopen($filePath, 'r')) !== FALSE) {
                // Ignore Header
                @fgetcsv($handle, 4096, $config['delimiter']);
                
                $i = 0;
                $skillVectors = []; // Initialize vector-array for embeddings

                while (($row = @fgetcsv($handle, 4096, $config['delimiter'])) !== FALSE) {
                    //-- Ensure doctrine is open
                    $this->ensureEntityManagerIsOpen();

                    $name          = $getVal($row, $config['mapping']['name'] ?? null);
                    $altLabels     = $getVal($row, $config['mapping']['altLabels'] ?? null);
                    $externalCode  = $getVal($row, $config['mapping']['external_code'] ?? null);
                    $canonicalName = $getVal($row, $config['mapping']['canonicalName'] ?? null);

                    if (empty($name)) {
                        continue;
                    }

                    if (empty($canonicalName)) {
                        $canonicalName = $name;
                    }

                    $slug     = strtolower($this->slugger->slug($name));
                    $slugHash = md5($slug);
                    $codeHash = !empty($externalCode) ? md5($externalCode) : null;

                    if ($codeHash && isset($processedCodes[$codeHash])) {
                        continue;
                    }
                    if (isset($processedSlugs[$slugHash])) {
                        continue;
                    }

                    //--------------------------------
                    // Creating through Matcher
                    //--------------------------------

                    try {
                        [$skill, $vector] = $this->matcher->findOrCreateSkill(
                            name: $name,
                            locale: $locale,
                            escoUri: $source === 'esco' ? $externalCode : null,
                            onetCode: $source === 'onet' ? $externalCode : null,
                            canonicalName: $canonicalName,
                            shouldFlush: false, 
                            shouldIndex: false, 
                            enableVectorSearch: $enableVectorSearch
                        );

                        if (!empty($vector)) {
                            $skillVectors[] = [
                                'id'     => $skill->getId(),
                                'name'   => $canonicalName,
                                'vector' => $vector,
                            ];
                        }
                    }
                    catch (\Exception $e) {
                        $failedEntityCount++;
                        
                        $this->ensureEntityManagerIsOpen();
                        continue;
                    }

                    if ($codeHash) {
                        $processedCodes[$codeHash] = true;
                    }

                    $processedSlugs[$slugHash] = true;

                    //--------------------------------
                    // Synonym/Alias Management
                    //--------------------------------
                    if (!empty($altLabels)) {
                        $aliases = preg_split('/[\r\n,|]+/', $altLabels);
                        foreach ($aliases as $aliasName) {
                            $aliasName = trim($aliasName);
                            if (empty($aliasName) || $aliasName === $name) {
                                continue;
                            }

                            $aliasHash = md5(strtolower($aliasName));

                            if (isset($processedAliases[$aliasHash])) {
                                continue;
                            }

                            $alias = new SkillAliasEntity(
                                alias: $aliasName,
                                skill: $skill
                            );   
                            $this->em->persist($alias);
                            $processedAliases[$aliasHash] = true;
                        }
                    }

                    $i++;
                    $totalImported++;

                    //--------------------------------
                    // Batch Flush & Indexation
                    //--------------------------------
                    if ($i % $batchSize === 0) {
                        try {
                            // Flush SQL
                            $this->em->flush();
                            $this->em->clear();

                            //-- Indexation
                            if ($this->vectorService) {
                                foreach ($skillVectors as $item) {
                                    $this->vectorService->indexSkill(
                                        skillId: $item['id'],
                                        skillName: $item['name'],
                                        vector: $item['vector']
                                    );
                                }
                            }
                        } catch (\Exception $e) {
                            $this->em->clear();
                            $failedEntityCount += count($skillVectors);

                            $this->ensureEntityManagerIsOpen();
                        }

                        //-- free vectors container
                        $skillVectors = [];
                        gc_collect_cycles();

                        $output->writeln("[$source] Imported: $i skills...");
                    }
                }
                
                fclose($handle);
                
                //--------------------------------
                // Final Flush
                //--------------------------------
                try {
                    $this->ensureEntityManagerIsOpen();
                    $this->em->flush();
                    $this->em->clear();

                    if ($this->vectorService) {
                        foreach ($skillVectors as $item) {
                            $this->vectorService->indexSkill(
                                skillId: $item['id'],
                                skillName: $item['name'],
                                vector: $item['vector']
                            );
                        }
                    }
                } catch (\Exception $e) {
                    $this->em->clear();
                }

                $skillVectors = [];
                gc_collect_cycles();
            }
        }

        $output->writeln(
            "<info>[$source] Import successful! ($totalImported skills added)"
            . ($failedEntityCount > 0 ? " $failedEntityCount entities generated an error!!" : "")
            . "</info>"
        );

        return Command::SUCCESS;
    }

    private function ensureEntityManagerIsOpen(): void
    {
        if (!$this->em->isOpen()) {
            $this->em = $this->doctrine->resetManager();
        }
    }
}