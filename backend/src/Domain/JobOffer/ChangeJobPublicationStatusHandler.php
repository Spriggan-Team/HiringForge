<?php

namespace App\Domain\JobOffer;


final readonly class ChangeJobPublicationStatusHandler
{
    public function __construct(
        private JobOfferRepositoryInterface $repository
    ) {}

    public function handle(string $jobId, JobPublicationStatus $prevStatus, JobPublicationStatus $newStatus): void
    {
        //-- Transition
        if (!$prevStatus->canTransition($newStatus)) {
            throw new \LogicException("Transition impossible.");
        }

        // bdd verification
        if ($newStatus === JobPublicationStatus::DRAFT && !$this->repository->canDefineAsDraft($jobId)) {
            throw new \DomainException("Cannot save as a draft (existing applications or closed job posting).");
        }

        // Targeted UPDATE in a database (e.g., UPDATE job_offer SET status = :newStatus WHERE id = :id AND status = :prevStatus)
        $updated = $this->repository->updatePublicationStatusDirectly($jobId, $prevStatus, $newStatus);

        if (!$updated) {
            throw new \LogicException("The status of the offer did not match the expected status.");
        }
    }
}