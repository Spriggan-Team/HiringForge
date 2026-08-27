<?php

namespace App\Application\Usecases\JobOffer;

use App\Domain\JobOffer\ChangeJobPublicationStatusHandler;
use App\Domain\JobOffer\JobOfferRepositoryInterface;
use App\Domain\JobOffer\JobPublicationStatus;



final readonly class MarkJobOfferAsDraft
{
    public function __construct(
        private JobOfferRepositoryInterface $jobOfferRepository
    ) {}

    /**
     * Pass an existing job offer back to draft status.
     * 
     * @param string $accountId The ID of the current user/actor
     * @param string $offerId   The target job offer ID
     * @throws DomainException|\LogicException
     */
    public function execute(string $accountId, string $offerId): void
    {
        //  Verification of Ownership / User Rights
        $this->jobOfferRepository->assertRelationWithUser($accountId, $offerId);

        // Verification of business requirements (no applications, etc.)
        if (!$this->jobOfferRepository->canDefineAsDraft($offerId)) {
            throw new \DomainException("Cannot set this job offer as draft (it may be closed or already has applications).");
        }

        // Here, we assume that the target status is DRAFT and that we are switching from PUBLISHED
        $success = $this->jobOfferRepository->updatePublicationStatusDirectly(
            jobId: $offerId,
            prevStatus: JobPublicationStatus::PUBLISHED,
            newStatus: JobPublicationStatus::DRAFT
        );

        if (!$success) {
            throw new \LogicException("Failed to update job status. The offer may not be in published state anymore.");
        }
    }
}