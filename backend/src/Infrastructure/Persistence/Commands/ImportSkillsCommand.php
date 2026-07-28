<?php

namespace App\Infrastructure\Persistence\Commands;

use App\Infrastructure\Persistence\Service\SkillMatcherService;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillTranslationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillAliasEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillEntity;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\String\Slugger\SluggerInterface;


#[AsCommand(name: 'app:import-skills', description: 'Import skills ESCO et O*NET into bdd')]
class ImportSkillsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private SluggerInterface $slugger,
        private SkillMatcherService $matcher
    ) {
        parent::__construct(); 
    }

    protected function configure(): void
    {
        $this->addOption('source', 's', InputOption::VALUE_OPTIONAL, 'Source à importer (esco ou onet)', 'esco');
    }
    

protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //--------------------------------
        // Configuration
        //--------------------------------

        // 1. Disable SQL Logging
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        // 2. Disable Symfony Stopwatch / DBAL Debug Middleware (Fixes StopwatchEvent memory leak)
        $config = $this->em->getConnection()->getConfiguration();
        if (method_exists($config, 'getMiddlewares') && method_exists($config, 'setMiddlewares')) {
            $middlewares = array_filter(
                $config->getMiddlewares(),
                fn($middleware) => !($middleware instanceof \Symfony\Bridge\Doctrine\Middleware\Debug\Middleware)
            );
            $config->setMiddlewares($middlewares);
        }

        $source = $input->getOption('source');

        $config = match ($source) {
            'esco' => [
                'paths' => [
                    __DIR__ . "/../../../data/csv/esco/skills_fr.csv",
                ],
                'mapping' => [
                    'name'          => 5, //preferredLabel
                    'altLabels'     => 6,
                    'external_code' => 2,
                    'canonicalName' => 5, // preferredLabel (À ajuster si besoin)
                ],
                'locale'    => 'fr',
                'delimiter' => ',',
            ],
            'onet' => [
                'paths' => [
                    __DIR__ . "/../../../data/csv/onet/onet_software_skills.csv",
                ],
                'mapping' => [
                    'name'          => 3, // Workplace Example (Ex: Adobe Acrobat)
                    'altLabels'     => 5, // Element Name (Ex: Document management software)
                    'external_code' => 1, // O*NET-SOC Code (Ex: 11-1011.00)
                    'canonicalName' => 3, // Workplace Example (Nom officiel/canonique O*NET)
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

        //--------------------------------
        // Pre-loading DB Caches (Memory Optimization)
        //--------------------------------

        $output->writeln("<info>Pre-loading database cache to prevent memory leaks...</info>");

        // Pre-load all existing aliases into a lightweight MD5 map
        $processedAliases = [];
        $rawAliases = $this->em->getConnection()
            ->fetchAllAssociative('SELECT LOWER(alias) as alias FROM skill_aliases');

        foreach ($rawAliases as $row) {
            $processedAliases[md5($row['alias'])] = true;
        }
        unset($rawAliases);

        // Pre-load all existing slugs
        $processedSlugs = [];
        $rawSlugs = $this->em->getConnection()
            ->fetchAllAssociative('SELECT slug FROM skill_translations');
            
        foreach ($rawSlugs as $row) {
            $processedSlugs[md5($row['slug'])] = true;
        }
        unset($rawSlugs);


        // Pre-load all existing external codes
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
                while (($row = @fgetcsv($handle, 4096, $config['delimiter'])) !== FALSE) {
                    $name          = $getVal($row, $config['mapping']['name'] ?? null);
                    $altLabels     = $getVal($row, $config['mapping']['altLabels'] ?? null);
                    $externalCode  = $getVal($row, $config['mapping']['external_code'] ?? null);
                    $canonicalName = $getVal($row, $config['mapping']['canonicalName'] ?? null);

                    if (empty($name)) {
                        continue;
                    }

                    // Fallback au cas où canonicalName est vide dans la ligne
                    if (empty($canonicalName)) {
                        $canonicalName = $name;
                    }

                    $slug     = strtolower($this->slugger->slug($name));
                    $slugHash = md5($slug);
                    $codeHash = !empty($externalCode) ? md5($externalCode) : null;

                    // Memory cache verification (via MD5)
                    if ($codeHash && isset($processedCodes[$codeHash])) {
                        continue;
                    }
                    if (isset($processedSlugs[$slugHash])) {
                        continue;
                    }

                    //--------------------------------
                    // Creating through Matcher
                    //--------------------------------

                    // Create via Matcher (WITHOUT Fuzzy Match to save RAM)
                    $skill = $this->matcher->findOrCreateSkill(
                        name: $name,
                        locale: $locale,
                        escoUri: $source === 'esco' ? $externalCode : null,
                        onetCode: $source === 'onet' ? $externalCode : null,
                        canonicalName: $canonicalName
                    );

                    $this->em->persist($skill);

                    if ($codeHash) {
                        $processedCodes[$codeHash] = true;
                    }

                    // Mark as processed immediately in the PHP cache
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

                            // Fast in-memory check (0 DB queries)
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
                    // Batch Flush
                    //--------------------------------

                    if ($i % $batchSize === 0) {
                        $this->em->flush();
                        $this->em->clear();
                        gc_collect_cycles(); // Purge RAM
                        $output->writeln("[$source] Imported: $i skills...");
                    }
                }
                
                fclose($handle);
                $this->em->flush();
                $this->em->clear();
                gc_collect_cycles();
            }
        }

        $output->writeln("<info>[$source] Import successful! ($totalImported skills added)</info>");

        return Command::SUCCESS;
    }
}