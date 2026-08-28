<?php

namespace App\Domain\JobOffer;

interface JobOfferImageRepositoryInterface
{
    /**
     * Turn a global image into a specific one
     */
    public function unsetMainImage(string $jobId): void;

    /**
     * Retrieve the main image
     */
    public function getMainImage(string $jobId): ?JobOfferImage;

    /**
     * Associate images with a job
     *
     * @param string $offerId
     * @param array<int, JobOfferImage> $images
     */
    public function associateImagesWithJob(string $offerId, array $images): void;

    /**
     * Check if images are associated with a job
     *
     * @param string $offerId
     * @param array<int, string> $images File IDs
     * @throws \Exception When one of the files does not match
     */
    public function isImagesAssociatedWithJob(string $offerId, array $images): bool;

    /**
     * Remove images from a job
     *
     * @param string $offerId
     * @param array<int, string> $images File IDs
     */
    public function removeImagesFromJob(string $offerId, array $images): void;
}
