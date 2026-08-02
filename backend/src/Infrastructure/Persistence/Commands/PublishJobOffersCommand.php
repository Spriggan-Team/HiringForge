<?php

namespace App\Infrastructure\Persistence\Commands;

use App\Domain\JobOffer\JobOffer;
use App\Domain\JobOffer\JobOfferRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;



#[AsCommand(
    name: 'app:publish-job-offers',
    description: 'Publie automatiquement les offres d\'emploi programmées pour le futur dont la date de début est atteinte.'
)]
class PublishJobOffersCommand extends Command
{
    private const SYSTEM_USER_ID = 'SYSTEM'; // Identifiant d'audit pour les actions automatiques


    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly JobOfferRepositoryInterface $repository,
    ) {
        parent::__construct();
    }


    protected function configure(): void
    {
        $this->addOption(
            'jobId',
            'id',
            InputOption::VALUE_OPTIONAL,
            'ID optionnel d\'une offre spécifique à publier immédiatement.'
        );
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $jobId = $input->getOption('jobId');

        $io->title('Traitement des publications d\'offres d\'emploi');

        // Case 1 :  Processing a specific offer passed as an argument
        if ($jobId) {
            if (!$this->repository->exists($jobId)) {
                $io->error(sprintf('L\'offre d\'emploi avec l\'ID "%s" n\'existe pas.', $jobId));
                return Command::FAILURE;
            }

            if (!$this->repository->isPublicationPending($jobId)) {
                $io->warning(sprintf('L\'offre d\'emploi avec l\'ID "%s" n\'est pas en attente de publication.', $jobId));
                return Command::SUCCESS;
            }

            $this->repository->publish($jobId, self::SYSTEM_USER_ID);
            $this->em->flush();

            $io->success(sprintf('L\'offre ID "%s" a été publiée avec succès.', $jobId));
            return Command::SUCCESS;
        }

        // Case 2 :  Global Cron Job (All Pending Offers Due)
        /** @var array<int, JobOffer> $pendingOffers */
        $pendingOffers = $this->repository->findPendingPublications();

        if (empty($pendingOffers)) {
            $io->info('Aucune offre d\'emploi à publier pour le moment.');
            return Command::SUCCESS;
        }

        $publishedCount = 0;
        foreach ($pendingOffers as $offer) {
            //-- Use the business method defined in the repository
            $this->repository->publish($offer->id(), self::SYSTEM_USER_ID);
            $publishedCount++;
        }

        $this->em->flush();

        $io->success(sprintf('%d offre(s) d\'emploi ont été publiée(s) avec succès.', $publishedCount));

        return Command::SUCCESS;
    }
}