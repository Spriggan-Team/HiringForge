<?php


namespace App\Application\Usecases\JobOffer;


use App\Domain\File\MediaStorageInterface;
use App\Domain\JobOffer\JobOfferImageRepositoryInterface;
use App\Domain\JobOffer\JobOfferRepositoryInterface;
use App\Domain\Shared\AccountStorageParams;
use App\Domain\User\UserRepositoryInterface;
use Psr\Log\LoggerInterface;



/**
 * @deprecated This class is not longer up to date with new feature
 * shouuldn't be used in its currents state 
 */
class JobOfferImageRemover
{
    public function __construct(
        private LoggerInterface $logger,
        private JobOfferRepositoryInterface $jobRepository,
        private MediaStorageInterface $mediaStorage,
        private UserRepositoryInterface $userRepository,
        private JobOfferImageRepositoryInterface $jobOfferImageRepository
    )
    {}

    /**
     * @deprecated
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

        $companyId = $this->userRepository->getOrganizationId($accountId);

        foreach ($fileNames as $uniqName) {
            $fileNotRemoved = true;

            // Attempt to delete from storage
            for ($attempt = 0; $attempt < 2 && $fileNotRemoved; $attempt++) {
                $this->mediaStorage->remove(
                    params: AccountStorageParams::companyJobImages(
                        companyId: $companyId,
                        storedFileName: $uniqName
                    ),
                    successCallback: function () use (&$fileNotRemoved) {
                        $fileNotRemoved = false;
                    }
                );
            }

            // If the physical deletion was successful, the image is detached from the database
            if (!$fileNotRemoved) {
                // $this->jobOfferImageRepository->(
                //     offerId: $offerId,
                //     fileName: $uniqName
                // );
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