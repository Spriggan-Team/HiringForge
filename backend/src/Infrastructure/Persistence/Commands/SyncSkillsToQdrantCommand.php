<?php

namespace App\Infrastructure\Persistence\Commands;

use App\Domain\Shared\Services\EmbeddingProviderInterface;
use App\Domain\Shared\Services\VectorServiceInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillEntity;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


#[AsCommand(
    name: 'app:qdrant:sync-skills',
    description: 'Synchronize skills from the database to Qdrant by generating embeddings via Ollama (bge-m3)'
)]
class SyncSkillsToQdrantCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private EmbeddingProviderInterface $embeddingService,
        private VectorServiceInterface $vectorService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Synchronizing skills with Qdrant (via Ollama bge-m3)');

        $skills = $this->entityManager->getRepository(SkillEntity::class)->findAll();

        if (empty($skills)) {
            $io->warning('No skills found in the database. Did you run the fixtures??');
            return Command::FAILURE;
        }

        $points = [];
        foreach ($skills as $index => $skill) {
            $textToEmbed = $skill->getCanonicalName();
            $io->text(sprintf('Generating embedding: "%s"...', $textToEmbed));

            try {
                $vector = $this->embeddingService->generateEmbedding($textToEmbed);

                $points[] = [
                    'id' => $skill->getId(), // Id
                    'vector' => $vector,
                    'payload' => [
                        'canonical_name' => $textToEmbed,
                        'skill_id' => $skill->getId(),
                    ],
                ];
            }
            catch (\Exception $e) {
                $io->error(sprintf('Error with Ollama regarding the skill %s : %s', $textToEmbed, $e->getMessage()));
                return Command::FAILURE;
            }
        }

        // Save vector into Qdrant
        $io->text('Sending Vectors to Qdrant...');
        try{
            $this->vectorService->upsretSkillBatch(
                points: $points,
            );
            $io->success(sprintf('%d Skills successfully synchronized in Qdrant!', count($points)));
            return Command::SUCCESS;
        }
        catch(\Throwable $e){
            $io->error('Failed to insert points into Qdrant : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}