<?php

namespace App\Domain\JobOffer;

use App\Domain\File\StaticMedia;
use App\Domain\JobOffer\JobOffer;
use App\Domain\Shared\Account\AccountId;

interface JobOfferRepositoryInterface
{  
public function exists(string $id): bool;

    /**
     * Verifies that a job offer exists in the database and is linked to an existing user.
     *
     * @throws \DomainException|\Exception If the offer does not exist or the relation is invalid.
     */
    public function assertRelationWithUser(string $accountId, string $offerId): void;

    /** Check if a job's scheduled publication date has passed */
    public function hasPublicationDatePassed(string $id): bool;

    /** @return JobOffer[] */
    public function findPendingPublications(): array;

    public function isPublicationPending(string $id): bool;


    /**
     * @throws RessourceNotFound
     */
    public function findById(string $accountId, string $offerId): JobOffer;

    /**
     * @return JobOffer[]
     */
    public function findAll(string $accountId, string $offerId): array;

    public function change(JobOffer $jobOffer, string $offerId, string $accountId): void;

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
     * @param array<int, JobOfferImage>
     */
    public function associateImagesWithJob(string $offerId, array $images): void;

    public function removeImageFromJob(string $offerId, string $fileName): void;
}