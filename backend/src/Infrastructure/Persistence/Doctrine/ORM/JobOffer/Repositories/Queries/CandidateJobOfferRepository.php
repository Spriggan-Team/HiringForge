<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Repositories\Queries;

use App\Application\Query\JobOffer\Repositories\CandidateJobOfferRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferViewEntity;
use Doctrine\ORM\EntityManagerInterface;
use Override;

class CandidateJobOfferRepository implements CandidateJobOfferRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $manager,
    ){}

    #[Override]
    public function hasViewed(
        string $candidateId,
        string $jobOfferId
    ): bool {
        $candidate = $this->manager->getReference(
            CandidateEntity::class,
            $candidateId
        );

        $jobOffer = $this->manager->getReference(
            JobOfferEntity::class,
            $jobOfferId
        );

        return $this->manager
            ->getRepository(JobOfferViewEntity::class)
            ->findOneBy([
                'candidate' => $candidate,
                'jobOffer' => $jobOffer,
            ]) !== null;
    }
  
}