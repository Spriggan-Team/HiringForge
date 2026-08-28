<?php

namespace App\Application\Usecases\JobOffer;

use App\Application\Usecases\UploadedFileInfo;
use App\Domain\Company\CompanyRepositoryInterface;
use App\Domain\File\MediaFactoryInterface;

use App\Domain\File\MediaStorageInterface;
use App\Domain\JobOffer\JobOfferImage;
use App\Domain\JobOffer\JobOfferImageRepositoryInterface;
use App\Domain\JobOffer\JobOfferRepositoryInterface;
use App\Domain\Shared\AccountStorageParams;


class JobOfferImageUploader
{
    public function __construct(
        private CompanyRepositoryInterface $companyRepository,
        private JobOfferRepositoryInterface $jobOfferRepository,
        private MediaFactoryInterface $mediaFactoryInterface,
        private JobOfferImageRepositoryInterface $jobOfferImageRepository,
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
     * @return array
     */
    public function execute(
        string $offerId,
        string $userId,
        array $files = [],
        ?int $mainImageIndex = null
    ): array {
        $this->jobOfferRepository->assertRelationWithUser(accountId: $userId, offerId: $offerId);

        $company = $this->companyRepository->fetchUserCompanyProjection(userId: $userId, scheme: ["id" => true]);

        if (!$company) {
            throw new \DomainException("No related company found for this user");
        }

        $companyId = is_array($company) ? 
                        ($company['id'] ?? null) 
                        : ($company->id ?? null);

        if (!$companyId) {
            throw new \DomainException("Invalid company identity structure");
        }

        $currentIndex = 0;
        $failedUploads = [];
        $successfulJobImageUpload = [];

        foreach ($files as $file) {
            $isMain = ($mainImageIndex !== null && $currentIndex === $mainImageIndex);

            $image = new JobOfferImage(
                media: $this->mediaFactoryInterface->createStaticMedia($file),
                isMain: $isMain
            );

            $this->mediaStorageInterface->store(
                file: $file,
                params: AccountStorageParams::companyJobImages(
                    companyId: (string) $companyId,
                    storedFileName:  $image->media->name
                ),
                successCallback: function () use (&$successfulJobImageUpload, $image) {
                    $successfulJobImageUpload[] = $image;
                },
                errorCallback: function ($result) use (&$failedUploads) {
                    $failedUploads[] = new UploadedFileInfo(
                        originalName: $result->originalName ?? 'unknown',
                        message: 'Failed to upload this file. Please try again'
                    );
                }
            );

            $currentIndex++;
        }

        if (!empty($successfulJobImageUpload)) {
            $this->jobOfferImageRepository->associateImagesWithJob($offerId, $successfulJobImageUpload);
        }

        return [
            'success' => $successfulJobImageUpload,
            'failed'  => $failedUploads,
        ];
    }
}