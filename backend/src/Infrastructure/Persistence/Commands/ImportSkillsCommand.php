<?php

namespace App\Infrastructure\Persistence\Commands;

use App\Domain\Shared\Services\VectorServiceInterface;
use App\Infrastructure\Services\Skills\SkillMatcherService;
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
        $this->addOption(
            'vector-search',
            'x',
            InputOption::VALUE_NONE,
            'Enable vector search'
        );
    }
    

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //--------------------------------
        // Configuration
        //--------------------------------

        //  Disable SQL Logging
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        //  Disable Symfony Stopwatch / DBAL Debug Middleware

        $config = $this->em->getConnection()->getConfiguration();
        if (method_exists($config, 'getMiddlewares') && method_exists($config, 'setMiddlewares')) {
            $middlewares = array_filter(
                $config->getMiddlewares(),
                fn($middleware) => !($middleware instanceof \Symfony\Bridge\Doctrine\Middleware\Debug\Middleware)
            );
            $config->setMiddlewares($middlewares);
        }

        $source = $input->getOption('source');
        $enableVectorSearch = $input->getOption('vector-search');


        $config = match ($source) {
            'esco' => [
                'paths' => [
                    __DIR__ . "/../../../data/csv/esco/skills_fr.csv",
                ],
                'mapping' => [
                    'name'          => 4, // Colonne E : preferredLabel
                    'altLabels'     => 5, // Colonne F : altLabels
                    'external_code' => 1, // Colonne B : conceptUri (URI uniqu)
                    'canonicalName' => 4, // Colonne E : preferredLabel
                ],
                'locale'    => 'fr',
                'delimiter' => ',',
            ],
            'onet' => [
                'paths' => [
                    __DIR__ . "/../../../data/csv/onet/onet_software_skills.csv",
                ],
                'mapping' => [
                    'name'          => 2,    // Colonne C : Nom du logiciel/compétence
                    'canonicalName' => 2,    // Same as name
                    'altLabels'     => null, 
                    'external_code' => 0,    // Colonne A
                    'category'      => 4,    // Colonne E : Catégory (ex: Spreadsheet software)
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

        $getVal = function (array $row, ?int $index): string {
            if ($index === null || $index < 0) {
                return '';
            }
            return isset($row[$index]) ? trim(mb_convert_encoding($row[$index], 'UTF-8', 'UTF-8')) : '';
        };

        //---------------
        //--- Count
        //-----------------

        $batchSize = 200;
        $totalRowsRead = 0;
        $totalImported = 0;
        $skippedEmpty  = 0;
        $skippedExisting = 0;
        $skippedDuplicate = 0;
        $failedEntityCount = 0;

        //--------------------------------
        // Pre-loading DB Caches
        //--------------------------------

        $output->writeln("<info>Pre-loading database cache to prevent memory leaks...</info>");

        $processedAliases = [];
        $rawAliases = $this->em->getConnection()->fetchAllAssociative('SELECT LOWER(alias) as alias FROM skill_aliases');

        foreach ($rawAliases as $row) {
            $processedAliases[md5($row['alias'])] = true;
        }
        unset($rawAliases);


        $processedCodes = [];
        $codeColumn = ($source === 'esco') ? 'esco_uri' : 'onet_code';

        $rawCodes = $this->em->getConnection()->fetchAllAssociative("SELECT $codeColumn as code FROM skills WHERE $codeColumn IS NOT NULL");

        foreach ($rawCodes as $row) {
            $processedCodes[md5($row['code'])] = true;
        }
        unset($rawCodes);


        $processSlugsInRun = []; //-- Local cache for dealing with slug


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
                    $totalRowsRead++;
                    if ($totalRowsRead % 200 === 0) {
                        $output->writeln("[$source] Rows processed: $totalRowsRead (Imported: $totalImported | Existing: $skippedExisting | Empty: $skippedEmpty | Duplicates: $skippedDuplicate)");
                    }

                    //-- Ensure doctrine is open
                    $this->ensureEntityManagerIsOpen();

                    $name          = $getVal($row, $config['mapping']['name'] ?? null);
                    $altLabels     = $getVal($row, $config['mapping']['altLabels'] ?? null);
                    $externalCode  = $getVal($row, $config['mapping']['external_code'] ?? null);
                    $canonicalName = $getVal($row, $config['mapping']['canonicalName'] ?? null);

                    if (empty($name)) {
                        $skippedEmpty++;
                        continue;
                    }

                    if (empty($canonicalName)) {
                        $canonicalName = $name;
                    }

                    $slug     = strtolower($this->slugger->slug($name));
                    $slugHash = md5($slug);
                    $codeHash = !empty($externalCode) ? md5($externalCode) : null;

                    if ($codeHash && isset($processedCodes[$codeHash])) {
                        $skippedExisting++;
                        continue;
                    }

                    if(isset($processSlugsInRun[$slugHash])){ // Avoid duplicate slug
                        $skippedDuplicate++;
                        continue;
                    }

                    //--------------------------------
                    // Creating through Matcher
                    //--------------------------------

                    try {
                        [$skill, $vector] = $this->matcher->findOrCreateSkill(
                            name: $name,
                            canonicalName: $canonicalName,
                            locale: $locale,
                            escoUri: $source === 'esco' ? $externalCode : null,
                            onetCode: $source === 'onet' ? $externalCode : null,
                            skillKey: $slugHash,
                            shouldFlush: false, 
                            shouldIndex: false, 
                            iaValidation: true,
                            enableVectorSearch: (bool)$enableVectorSearch,
                            allowAutoBatchProcessing: true,
                            output: $output
                        );

                        $processSlugsInRun[$slugHash] = true;

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
                        if ($failedEntityCount <= 5) {
                            $output->writeln("<error>Error on row: " . $e->getMessage() . "</error>");
                        }
                        $this->ensureEntityManagerIsOpen();
                        continue;
                    }


                    if ($codeHash) {
                        $processedCodes[$codeHash] = true;
                    }


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

                            if(!$this->em->getRepository(SkillAliasEntity::class)->findOneBy([ "alias" => $aliasName ])){
                                $alias = new SkillAliasEntity(
                                    alias: $aliasName,
                                    skill: $skill
                                );   
                                $this->em->persist($alias);
                                $processedAliases[$aliasHash] = true;
                            }

                        }
                    }

                    $i++;
                    $totalImported++;

                    //--------------------------------
                    // Batch Flush & Indexation
                    //--------------------------------
                    if ($i % $batchSize === 0) {
                        try {
                            $this->em->flush();

                            if ($this->vectorService) {
                                foreach ($skillVectors as $item) {
                                    if(empty($item['id'])){
                                        continue;
                                    }
                                    $this->vectorService->indexSkill(
                                        skillId: $item['id'],
                                        skillName: $item['name'],
                                        vector: $item['vector']
                                    );
                                }
                            }

                            $this->em->clear();
                            $this->matcher->clearPendingBatchCache();
                        }
                        catch (\Exception $e) {
                            $output->writeln("<error>Batch Error: " . $e->getMessage() . "</error>");

                            $this->em->clear();
                            $this->matcher->clearPendingBatchCache();
                            $failedEntityCount += count($skillVectors);
                            $this->ensureEntityManagerIsOpen();
                        }

                        $skillVectors = [];
                        gc_collect_cycles();
                        $output->writeln("[$source] Imported: $totalImported skills... (Skipped -> Empty: $skippedEmpty | Existing: $skippedExisting | Duplicates: $skippedDuplicate)");
                    }
                }

                
                fclose($handle);
                
                //--------------------------------
                // Final Flush
                //--------------------------------
                try {
                    $this->ensureEntityManagerIsOpen();
                    $this->em->flush();

                    if ($this->vectorService) {
                        foreach ($skillVectors as $item) {
                            if(!empty($item['id'])){
                                $this->vectorService->indexSkill(
                                    skillId: $item['id'],
                                    skillName: $item['name'],
                                    vector: $item['vector']
                                );
                            }
                        }
                    }
                    $this->em->clear();
                }
                catch (\Exception $e) {
                    $output->writeln("<error>Final Batch Error: " . $e->getMessage() . "</error>");
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