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
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
    }

    protected function configure(): void
    {
        $this->addOption('source', 's', InputOption::VALUE_OPTIONAL, 'Source à importer (esco ou onet)', 'esco');
    }

    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $source = $input->getOption('source');

        //--------------------------------
        // Configuration
        //--------------------------------

        $config = match ($source) {
            'esco' => [
                'paths' => [
                    __DIR__ . "/../../../data/csv/esco/skills_fr.csv",
                ],
                'mapping' => [
                    'name'          => 5, // Column Excel E (1-based index)
                    'altLabels'     => 6, // Column Excel F
                    'external_code' => 2, // Column Excel B (escoUri)
                ],
                'locale'    => 'fr',
                'delimiter' => ',',
            ],
            'onet' => [
                'paths' => [
                    __DIR__ . "/../../../data/csv/onet/onet_software_skills.csv",
                ],
                'mapping' => [
                    'name'          => 3, // Column Excel C (1-based index)
                    'altLabels'     => 5, // Column Excel E
                    'external_code' => 2, // Column Excel B (onetCode)
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

        // Helper conversion index Excel -> PHP
        $getVal = function (array $row, ?int $excelColumn): string {
            if ($excelColumn === null || $excelColumn < 1) {
                return '';
            }
            $phpIndex = $excelColumn - 1;
            return isset($row[$phpIndex]) ? trim(mb_convert_encoding($row[$phpIndex], 'UTF-8', 'UTF-8')) : '';
        };

        $totalImported = 0;
        $batchSize = 200;

        // Local cache to avoid the duplicate trap within the same batch
        $processedCodes = [];
        $processedSlugs = [];
        $processedAliases = [];

        //--------------------------------
        //----- SEEDINDS
        //--------------------------------

        foreach ($config['paths'] as $filePath) {
            if (!file_exists($filePath)) {
                $output->writeln("<error>Fichier introuvable : $filePath</error>");
                continue;
            }

            if (($handle = fopen($filePath, 'r')) !== FALSE) {
                // Ignore Header
                fgetcsv($handle, 4096, $config['delimiter']);
                
                $i = 0;
                while (($row = fgetcsv($handle, 4096, $config['delimiter'])) !== FALSE) {
                    $name         = $getVal($row, $config['mapping']['name'] ?? null);
                    $altLabels    = $getVal($row, $config['mapping']['altLabels'] ?? null);
                    $externalCode = $getVal($row, $config['mapping']['external_code'] ?? null);

                    if (empty($name)) {
                        continue;
                    }

                    $slug = strtolower($this->slugger->slug($name));

                    // Check the current memory cache
                    if (!empty($externalCode) && isset($processedCodes[$externalCode])) {
                        continue;
                    }

                    if (isset($processedSlugs[$slug])) {
                        continue;
                    }

                    // SQL Database Validation
                    $existingSkill = null;
                    if (!empty($externalCode)) {
                        $codeField = ($source === 'esco') ? 'escoUri' : 'onetCode';
                        $existingSkill = $this->em->getRepository(SkillEntity::class)->findOneBy([$codeField => $externalCode]);
                    }

                    if (!$existingSkill) {
                        $existingTranslation = $this->em->getRepository(SkillTranslationEntity::class)->findOneBy(['slug' => $slug]);
                        if ($existingTranslation) {
                            $existingSkill = $existingTranslation->getSkill();
                        }
                    }

                    if ($existingSkill) {
                        // We store this in memory cache for future instances of the CSV
                        if (!empty($externalCode)) {
                            $processedCodes[$externalCode] = true;
                        }
                        $processedSlugs[$slug] = true;
                        continue;
                    }

                    //--------------------------------
                    // Creating through Matcher
                    //--------------------------------

                    $skill = $this->matcher->findOrCreateSkill(
                        name: $name,
                        locale: $locale,
                        escoUri: $source === 'esco' ? $externalCode : null,
                        onetCode: $source === 'onet' ? $externalCode : null
                    );

                    $this->em->persist($skill);

                    // Mark as processed immediately in the PHP cache
                    if (!empty($externalCode)) {
                        $processedCodes[$externalCode] = true;
                    }
                    $processedSlugs[$slug] = true;

                    //--------------------------------
                    //-- Synonym/Alias Management
                    //----------------------------------

                    if (!empty($altLabels)) {
                        $aliases = preg_split('/[\r\n,|]+/', $altLabels);
                        foreach ($aliases as $aliasName) {
                            $aliasName = trim($aliasName);
                            
                            if (empty($aliasName) || $aliasName === $name) {
                                continue;
                            }

                            $aliasKey = strtolower($aliasName);

                            // Check the local memory cache
                            if (isset($processedAliases[$aliasKey])) {
                                continue;
                            }

                            // SQL Database Validation
                            $existingAlias = $this->em->getRepository(SkillAliasEntity::class)->findOneBy(['alias' => $aliasName]);
                            
                            if ($existingAlias) {
                                $processedAliases[$aliasKey] = true;
                                continue;
                            }

                            //-- Create the alias if it does not exist anywhere
                            $alias = new SkillAliasEntity(
                                alias: $aliasName,
                                skill: $skill
                            );  
                            $this->em->persist($alias);
                            
                            //--  Mark as processed in the memory cache
                            $processedAliases[$aliasKey] = true;
                        }
                    }

                    $i++;
                    $totalImported++;

                    //-----------------
                    //--  Batch Flush
                    //-------------------

                    if ($i % $batchSize === 0) {
                        $this->em->flush();
                        $this->em->clear();
                        $output->writeln("[$source] Importés : $i compétences...");
                    }
                }
                
                fclose($handle);
                $this->em->flush(); // Finale save
                $this->em->clear();
            }
        }

        $output->writeln("<info>[$source] Import effectué avec succès ! ($totalImported compétences ajoutées)</info>");

        return Command::SUCCESS;
    }
}