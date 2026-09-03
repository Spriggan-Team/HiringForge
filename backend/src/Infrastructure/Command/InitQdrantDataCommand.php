<?php


namespace App\Infrastructure\Command;

use Override;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;


#[AsCommand()]
class InitQdrantDataCommand extends Command
{
    private HttpClientInterface $httpClient;
    private string $qdrantHost;

    #[Override]
    public function __construct(?string $name = null, ?callable $code = null)
    {
        parent::__construct($name, $code);
        $this->qdrantHost = $_ENV['QDRANT_URL'] ?? 'http://qdrant:6333';
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
                // Still Tryng Communication with Qdrant 
            }

            sleep(1);
        }

        if (!$ready) {
            $io->error('Qrant doent respond after some sleeping time');
            return Command::FAILURE;
        }

        //-- Verify if collections exist
        try {
            $response = $this->httpClient->request('GET', $this->qdrantHost . '/collections/' . $collectionName);
            if ($response->getStatusCode() === 200) {
                $io->success("Collection << '{$collectionName}' >> already exist. No requrie actions.");
                return Command::SUCCESS;
            }
        }
        catch (\Exception $e) {
            // 404: if collections doesn't exist
        }

        // Restore existing snopschot
        $snapshotPath = '/qdrant_init_data/skills_snapshot.snapshot';
        $io->text("Restauration du snapshot de compétences...");
        try {
            $response = $this->httpClient->request('PUT', $this->qdrantHost . '/collections/' . $collectionName . '/snapshots/recover', [
                'json' => [
                    'location' => 'file://' . $snapshotPath
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $io->success("Snapshot restored with sucess !");
                return Command::SUCCESS;
            }
            else {
                $io->error("Failed to restore snapshot.");
                return Command::FAILURE;
            }
        }
        catch (\Exception $e) {
            $io->error("Error when calling for Qdrant : " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}