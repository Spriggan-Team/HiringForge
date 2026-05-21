<?php


namespace App\Application\Usecases\JobOffer;


use App\Domain\File\MediaOwnerType;
use App\Domain\File\MediaPurpose;
use App\Domain\File\MediaStorageInterface;
use App\Domain\JobOffer\JobOfferRepositioryInterface;



class JobOfferImageRemover
{
    public function __construct(
        private JobOfferRepositioryInterface $jobRepository,
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
        array $fileNames,
    ): void
    {
        $this->jobRepository->assertRelationWithUser(
            accountId: $accountId,
            offerId: $offerId
        );

        foreach ($fileNames as $uniqName) {

            $this->jobRepository->removeImageFromJob(
                offerId: $offerId,
                fileName: $uniqName
            );

            $fileNotRemoved = true;

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

            if ($fileNotRemoved)
            {
                // Optional: log or archive error, or throw exception
            }
        }
    }
}