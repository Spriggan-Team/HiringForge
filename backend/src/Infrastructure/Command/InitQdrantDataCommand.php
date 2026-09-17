<?php

namespace App\Infrastructure\Command;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:init-qdrant-data', 
    description: 'Initialise les données dans Qdrant'
)]
class InitQdrantDataCommand extends Command
{
    private string $qdrantHost;

    public function __construct(
        private HttpClientInterface $httpClient,
        ?string $name = null
    ) {
        parent::__construct($name);
        $this->qdrantHost = $_ENV['QDRANT_URL'] ?? 'http://qdrant:6333';
    }

    
    #[Override]
    protected function configure()
    {
        $this->addOption(
            'path', 'p', 
            InputOption::VALUE_OPTIONAL, 
            'Snapshot source', '/var/www/html/src/Infrastructure/docker/qdrant_init/skills_backup.snapshot'
        );
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $collectionName = 'skills';

        $io->title('Verify Qdrant relationel database...');

        // Await
        $io->text('Wait For disponibility Qdrant to be ready...');
        $maxTries = 30;
        $ready = false;

        for ($i = 0; $i < $maxTries; $i++) {
            try {
                $response = $this->httpClient->request('GET', $this->qdrantHost . '/readyz');

                if ($response->getStatusCode() === 200) {
                    $ready = true;
                    break;
                }
            }
            catch (\Exception $e) {
                // Still Trying Communication with Qdrant 
            }

            sleep(1);
        }

        if (!$ready) {
            $io->error('Qrant doesn\'t respond after some sleeping time');
            return Command::FAILURE;
        }

        //-- Verify if collections exist
        try {
            $response = $this->httpClient->request('GET', $this->qdrantHost . '/collections/' . $collectionName);
            if ($response->getStatusCode() !== 200) {
                $this->httpClient->request('PUT', $this->qdrantHost . '/collections/' . $collectionName, [
                    'json' => [
                        'vectors' => [
                            'size' => 1024, 
                            'distance' => 'Cosine'
                        ]
                    ]
                ]);
            }
        }
        catch (\Exception $e) {
            // Si la collection n'existe pas, on la crée
            $this->httpClient->request('PUT', $this->qdrantHost . '/collections/' . $collectionName, [
                'json' => [
                    'vectors' => [
                        'size' => 384,
                        'distance' => 'Cosine'
                    ]
                ]
            ]);
        }

        // -- File
        $localPath = $input->getOption("path");

        if (!file_exists($localPath)) {
            $io->error("Le fichier de snapshot est introuvable : " . $localPath);
            return Command::FAILURE;
        }

        $io->text("Restauration du snapshot par upload direct...");
        try {
            $response = $this->httpClient->request('POST', $this->qdrantHost . '/collections/' . $collectionName . '/snapshots/upload?priority=snapshot', [
                'body' => [
                    'snapshot' => fopen($localPath, 'r'),
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $io->success("Snapshot restored with success !");
                return Command::SUCCESS;
            } else {
                $io->warning("Le snapshot n'a pas pu être restauré (fichier potentiellement incompatible). La collection vide est prête.");
                return Command::SUCCESS;
            }
        }
        catch (\Exception $e) {
            $io->error("Error when calling Qdrant : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}