<?php

namespace App\Domain\JobOffer;

use App\Domain\JobOffer\JobOffer;
use App\Domain\Shared\Account\AccountId;

interface JobOfferRepositoryInterface
{  
    public function exists(string $id): bool;

    /**
     * Check if a job is able to accept publication
     */
    public function canAcceptApplications(string $jobOfferId): bool;

    /**
     * Verifies that a job offer exists in the database and is linked to an existing user (recruiter).
     * @param string $accountId the id of the user (recuiter)
     * @throws \DomainException|\Exception If the offer does not exist or the relation is invalid.
     */
    public function assertRelationWithUser(string $accountId, string $offerId): void;

    /** Check if a job's scheduled publication date has passed */
    public function hasPublicationDatePassed(string $id): bool;


    /**
     * retreive pending pub job
     *  @return JobOffer[] 
     */
    public function findPendingPublications(): array;

    
    /**
     * Check if a job is pending or not ...
     */
    public function isPublicationPending(string $id): bool;


    /**
     * Checks whether a job posting can be set to or reset to draft status.
     * A job posting that is closed or already has applicants cannot be set to draft status.
     */
    public function canDefineAsDraft(JobOffer|string $job): bool;


    /**
     * @throws RessourceNotFound
     */
    public function findById(string $accountId, string $offerId): JobOffer;

    /**
     * retreive Batch of job
     * @return JobOffer[]
     */
    public function findAll(string $accountId, string $offerId): array;


    public function getTitle(string $jobId): string;


    public function change(JobOffer $jobOffer, string $offerId, string $accountId): void;


    /**
     * Status update
     * 
     */
    public function updatePublicationStatusDirectly(string $jobId, JobPublicationStatus $prevStatus, JobPublicationStatus $newStatus);

    /**
     * Save job
     */
    public function save(JobOffer $offer, AccountId $accountId): void;

    
    public function delete(string $uuid, string $accountId): void;


    /**
     * Publishes a job offer.
     *
     * @param string $offerId The ID of the offer to publish.
     * @param string $userId The ID of the current actor (e.g., 'SYSTEM' for Cron).
     */
    public function publish(string $offerId, string $userId): void;


    /**
     * @param string $offerId
     * @param array<int, JobOfferImage> $images
     */
    public function associateImagesWithJob(string $offerId, array $images): void;
    

    public function removeImageFromJob(string $offerId, string $fileName): void;


    /**
     * Get company id based on job offerId
     */
    public function getCompanyId(string $jobOfferId): string;

    /**
     * Retreive the id of the author of this job offer (recruiter)
     */
    public function getAuthorId(string $jobId): string;
}