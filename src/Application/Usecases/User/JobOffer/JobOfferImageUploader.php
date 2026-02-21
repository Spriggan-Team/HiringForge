<?php

namespace App\Application\Usecases\User\JobOffer;

use App\Application\Usecases\UploadedFileInfo;
use App\Domain\File\MediaFactoryInterface;
use App\Domain\File\MediaOwnerType;
use App\Domain\File\MediaPurpose;
use App\Domain\File\MediaStorageInterface;
use App\Domain\JobOffer\JobOfferImage;
use App\Domain\JobOffer\JobOfferRepositioryInterface;

class JobOfferImageUploader
{
    public function __construct(
        private JobOfferRepositioryInterface $jobOfferRepository,
        private MediaFactoryInterface $mediaFactoryInterface,
        private MediaStorageInterface $mediaStorageInterface,
    )
    {}


    /**
     * Allows a user to upload one or more images related to a job offer.
     *
     * @param   mixed     $files   The uploaded file(s). The concrete type depends on the file handling implementation.
     * @param   string    $offerId The identifier of the job offer to link the images to.
     * @param   string    $userId  The identifier of the user who has initiated this action.
     * @throws \DomainException|\Exception 
     * @return UploadedFileInfo[]
     */
    public function execute(
        mixed $files = [],
        string $offerId,
        string $userId,
        int $mainImageIndex
    )
    {
        $this->jobOfferRepository->assertRelationWithUser($offerId, $userId);

        $currentIndex = 0;

        /** @var UploadedFileInfo */
        $failedUploads = [];

        /** @var JobOfferImage[] */
        $successfulJobImageUpload = [];

        foreach($files as $file)
        {
            $isMain = $currentIndex === $mainImageIndex;
            $image = new JobOfferImage(
                media: $this->mediaFactoryInterface->createStaticMedia($file),
                isMain: $isMain
            );
            if($image)
            {
                $this->mediaStorageInterface->store(
                    file: $file,
                    ownerId: $userId,
                    ownerType: MediaOwnerType::USER,
                    storedFileName: $image->media->name,
                    mediaPurpose: MediaPurpose::JOB_OFFER_IMAGE,
                    successCallback: function () use (&$jobOffer, $image){
                        $successfulJobImageUpload[] = $image;
                    },
                    errorCallback: function ($result) use (&$failedUploads){
                        $failedUploads[] = new UploadedFileInfo(
                                                originalName: $result->originalName,
                                                message: "Failed to upload this file. Please try again"
                                            );
                    }
                );
                
            }
            $currentIndex++;
        }

        $this->jobOfferRepository->associateImagesWithJob($offerId, $successfulJobImageUpload);

        return $failedUploads;
    }
}