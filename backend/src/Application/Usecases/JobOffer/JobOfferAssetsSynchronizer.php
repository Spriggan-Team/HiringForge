<?php

namespace App\Application\Usecases\JobOffer;


use App\Domain\File\MediaFactoryInterface;
use App\Domain\File\MediaStorageInterface;
use App\Domain\JobOffer\JobOfferImage;

use App\Domain\JobOffer\JobOfferImageRepositoryInterface;
use App\Domain\JobOffer\JobOfferRepositoryInterface;
use App\Domain\Shared\AccountStorageParams;

use App\Domain\Shared\TransactionManagerInterface;
use App\Domain\User\UserRepositoryInterface;


class JobOfferAssetsSynchronizer
{
    public function __construct(
        private JobOfferRepositoryInterface $jobRepository,
        private JobOfferImageRepositoryInterface $jobOfferImageRepository,
        private MediaFactoryInterface $mediaFactory,
        private UserRepositoryInterface $userRepository,
        private MediaStorageInterface $mediaStorage,
        private TransactionManagerInterface $transactionManager
    ) {}


    /**
     * @param string[] $removeIds IDs des fichiers à supprimer
     */
    public function execute(
        string $userId,
        string $jobId,
        array $files,
        array $removeIds = [],
        ?int $mainIndex = null
    ): void {
        // Access Control 
        $this->jobRepository->assertRelationWithUser(accountId: $userId, offerId: $jobId);

        if (!$this->jobRepository->isEditable($jobId)) {
            throw new \DomainException("Job offer is not editable");
        }

        
        if (!empty($removeIds) && !$this->jobOfferImageRepository->isImagesAssociatedWithJob(offerId: $jobId, images: $removeIds)) {
            throw new \DomainException("One of the removed images doesn't match requirements");
        }


        // Company storage parameters context
        $companyId = $this->userRepository->getOrganizationId($userId);
        $params = AccountStorageParams::companyImages(companyId: $companyId);


        //  Physical upload of new files + Domain Object instantiation
        /** @var JobOfferImage[] $newImages */
        $newImages = [];
        /** @var JobOfferImage[] $uploadedImagesTrack */
        $uploadedImagesTrack = [];

        try{
            foreach ($files as $index => $file) {
                $staticMedia = $this->mediaFactory->createStaticMedia($file);
                
                // Store physical file
                $this->mediaStorage->store(
                    params: $params,
                    file: $file,
                    storedFileName: $staticMedia->name
                );

                $image[] = new JobOfferImage(
                    media: $staticMedia,
                    isMain: ($index === $mainIndex)
                );

                $newImages[] = $image;
                $uploadedImagesTrack[] = $image;
            }
        }
        catch(\Exception $e){
            foreach ($uploadedImagesTrack as $uploadedImage) {
                $this->mediaStorage->remove(
                    params: $params,
                    mimeType: $uploadedImage->media->mime,
                    fileName: $uploadedImage->media->name
                );
            }
            throw $e;
        }



        // purge file meta data
        $deletedFiles = [];
        try{
            // Atomomic BDD Execurion
            $this->transactionManager->execute(function () use ($jobId, $removeIds, $mainIndex, $newImages, &$deletedFiles) {
                // BDD deletion
                if (!empty($removeIds)) {
                    $deletedFiles = $this->jobOfferImageRepository->removeImagesFromJob(offerId: $jobId, images: $removeIds);
                }

                // Update main image flag
                if ($mainIndex !== null && !empty($newImages)) {
                    $currentMain = $this->jobOfferImageRepository->getMainImage($jobId);
                    if ($currentMain !== null) {
                        $this->jobOfferImageRepository->unsetMainImage($jobId);
                    }
                }

                // Save new images metadata in BDD
                if (!empty($newImages)) {
                    $this->jobOfferImageRepository->associateImagesWithJob(offerId: $jobId, images: $newImages);
                }
            });
        }
        catch(\Exception $e)
        {
            foreach ($uploadedImagesTrack as $uploadedImage) {
                $this->mediaStorage->remove(
                    params: $params,
                    mimeType: $uploadedImage->media->mime,
                    fileName: $uploadedImage->media->name
                );
            }
            throw $e;
        }



        // Purging physical data of removed images (Only after successful DB commit)
        if (!empty($deletedFiles)) {
            foreach ($deletedFiles as $fileData) {
                $this->mediaStorage->remove(
                    params: $params,
                    mimeType: $fileData['mime'],
                    fileName: $fileData['name']
                );
            }
        }
    }
}