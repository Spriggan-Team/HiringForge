<?php


namespace App\Application\Usecases\JobOffer;


use App\Domain\File\MediaOwnerType;
use App\Domain\File\MediaPurpose;
use App\Domain\File\MediaStorageInterface;
use App\Domain\JobOffer\JobOfferRepositoryInterface;


use Psr\Log\LoggerInterface;


class JobOfferImageRemover
{
    public function __construct(
        private LoggerInterface $logger,
        private JobOfferRepositoryInterface $jobRepository,
        private MediaStorageInterface $mediaStorage,
    )
    {}

    /**
     * @param string $acountId   -  The account's id (the id of an user is to be expected)
     * @param string $offerId    -  The id of the linked offer
     * @param array  $fileNames  -  An array of uniq filename
     */
    public function execute(
        string $accountId,
        string $offerId,
        array $fileNames
    ): array {
        $this->jobRepository->assertRelationWithUser(
            accountId: $accountId,
            offerId: $offerId
        );

        $removedFiles = [];
        $failedFiles = [];

        foreach ($fileNames as $uniqName) {
            $fileNotRemoved = true;

            // Attempt to delete from storage
            for ($attempt = 0; $attempt < 2 && $fileNotRemoved; $attempt++) {
                $this->mediaStorage->remove(
                    uniqName: $uniqName,
                    ownerId: $accountId,
                    ownerType: MediaOwnerType::USER,
                    purpose: MediaPurpose::JOB_OFFER_IMAGE,
                    successCallback: function () use (&$fileNotRemoved) {
                        $fileNotRemoved = false;
                    }
                );
            }

            // If the physical deletion was successful, the image is detached from the database
            if (!$fileNotRemoved) {
                $this->jobRepository->removeImageFromJob(
                    offerId: $offerId,
                    fileName: $uniqName
                );
                $removedFiles[] = $uniqName;
            }
            else {
                $failedFiles[] = $uniqName;
                $this->logger?->error("Failed to remove job offer asset from storage", [
                    'offerId' => $offerId,
                    'accountId' => $accountId,
                    'fileName' => $uniqName,
                ]);
            }
        }

        return [
            'removed' => $removedFiles,
            'failed'  => $failedFiles,
        ];
    }
}