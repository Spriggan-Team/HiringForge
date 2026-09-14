<?php

namespace App\Application\Usecases\User;

use App\Application\DTO\Company\EditCompanyDto;
use App\Application\DTO\EditFileDto;
use App\Domain\File\MediaFactoryInterface;

use App\Domain\File\MediaStorageInterface;
use App\Domain\User\Siret;
use App\Domain\User\UserRepositoryInterface;

use App\Application\DTO\User\Edition\ChangeUserProfileCommand;
use App\Application\DTO\User\Edition\EditUserDto;
use App\Domain\Company\Company;
use App\Domain\Company\CompanyRepositoryInterface;

use App\Domain\Department\Department;
use App\Domain\Shared\AccountStorageParams;
use App\Domain\Shared\Address;
use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\TransactionManagerInterface;
use App\Domain\User\User;


class UserModifier
{
    public function __construct(
        private MediaStorageInterface $storage,
        private MediaFactoryInterface $mediaFactory,
        private UserRepositoryInterface $userRepository,
        private CompanyRepositoryInterface $companyRepository,
        private TransactionManagerInterface $transactionManager,
    ) {}


    /**
     * Modifies the authenticated user's profile.
     *
     * @return string[] List of files that could not be uploaded.
     */
    public function execute(ChangeUserProfileCommand $command): array
    {
        $failedUploads = [];

        $user = $this->userRepository->findById($command->uuid);

        if ($user === null) {
            throw new \RuntimeException('User not found.');
        }

        $company = $this->companyRepository->get(
            $user->companyId()
        );

        $this->transactionManager->execute(
            function () use (
                $command,
                $user,
                $company,
                &$failedUploads
            ): void {
                if ($command->profile->user !== null) {
                    $this->modifyUser(
                        $user,
                        $command->profile->user,
                        $failedUploads,
                    );
                }

                if (
                    $command->profile->company !== null
                    && $company !== null
                ) {
                    $this->modifyCompany(
                        $company,
                        $command->profile->company,
                        $failedUploads,
                    );
                }

                $this->userRepository->save($user);

                if ($company !== null) {
                    $this->companyRepository->save($company);
                }
            }
        );

        return $failedUploads;
    }



    private function modifyUser(
        User $user,
        EditUserDto $dto,
        array &$failedUploads,
    ): void {
        if ($dto->firstName !== null) {
            $user->changeFirstName($dto->firstName);
        }

        if ($dto->lastName !== null) {
            $user->changeLastName($dto->lastName);
        }

        if ($dto->description !== null) {
            $user->setDescription($dto->description);
        }

        if ($dto->email !== null) {
            $user->changeEmail(
                EmailAddress::create($dto->email)
            );
        }

        if ($dto->image !== null) {
            $this->modifyUserImage(
                $user,
                $dto->image,
                $failedUploads,
            );
        }
    }



    private function modifyCompany(
        Company $company,
        EditCompanyDto $dto,
        array &$failedUploads,
    ): void {
        if ($dto->name !== null) {
            $company->rename($dto->name);
        }

        if ($dto->siret !== null) {
            $company->changeSiret(
                Siret::create($dto->siret)
            );
        }

        if ($dto->description !== null) {
            $company->changeDescription($dto->description);
        }

        if ($dto->logoProvided) {
            if ($dto->logo !== null) {
                $this->modifyCompanyLogo(
                    $company,
                    $dto->logo,
                    $failedUploads,
                );
            }
            else {
                $company->setLogo(null);
            }
        }

        if ($dto->videoPresentation !== null) {
            $this->modifyCompanyVideo(
                $company,
                $dto->videoPresentation,
                $failedUploads,
            );
        }

        $this->modifyDepartments(
            $company,
            $dto,
        );

        $this->modifyLocations(
            $company,
            $dto,
        );

        $this->modifyImages(
            $company,
            $dto,
            $failedUploads,
        );
    }



    /**
     * --------------------
     * Helpers
     * --------------------
     */

    private function modifyLocations(
        Company $company,
        EditCompanyDto $dto,
    ): void {
        foreach ($dto->locationsAdded as $added) {
            $company->addAddress(
                Address::create(
                    city: $added->city,
                    country: $added->country,
                    postalCode: $added->postalCode,
                    street: $added->street,
                )
            );
        }

        foreach ($dto->locationsRemoved as $removedId) {
            $company->removeAddressById(
                id: $removedId,
            );
        }
    }


    private function modifyDepartments(
        Company $company,
        EditCompanyDto $dto,
    ): void {
        foreach ($dto->departmentsAdded as $added) {
            $company->addDepartment(
                Department::create(
                    label: $added->name,
                )
            );
        }

        foreach ($dto->departmentsRemoved as $removedId) {
            $company->removeDepartmentById(
                departmentId: $removedId,
            );
        }
    }



    private function modifyCompanyVideo(
        Company $company,
        EditFileDto $videoPresentation,
        array &$failedUploads,
    ): void {
        $video = $this->mediaFactory->createTimedMedia(
            $videoPresentation->file
        );

        $storageParams = AccountStorageParams::companyVideoPresentation(
            companyId: $company->id()
        );

        try {
            $this->storage->store(
                file: $videoPresentation->file,
                storedFileName: $video->name,
                params: $storageParams,
                successCallback: function ($result) use($storageParams, $video, $company): void {
                    $oldVideo = $company->videoPresentation();
                    if($oldVideo){
                        $this->storage->remove(
                            params: $storageParams,
                            fileName: $oldVideo->name,
                            mimeType: $oldVideo->mime
                        );
                    }
                    $company->addVideoPresentation($video);
                },
                errorCallback: function ($result) use (
                    $company,
                    &$failedUploads
                ): void {
                    $company->removeVideoPresentation();
                    $failedUploads[] = $result->originalName;
                },
            );
        }
        catch (\Throwable $exception) {
            $failedUploads[] = $video->originalName;
        }
    }



    private function modifyUserImage(
        User $user,
        EditFileDto $dto,
        array &$failedUploads,
    ): void {
        $userImage = $this->mediaFactory->createStaticMedia(
            $dto->file
        );

        $storageParams = AccountStorageParams::recruiterProfile(
            companyId: $user->companyId()
        );

        try {
            $this->storage->store(
                file: $dto->file,
                storedFileName: $userImage->name,
                params: $storageParams,
                successCallback: function ($result) use($user, $userImage, $storageParams): void {
                    $oldImage = $user->image();
                    if($oldImage){
                        $this->storage->remove(
                            params: $storageParams,
                            mimeType: $oldImage->mime,
                            fileName: $oldImage->name,
                            successCallback: function() use($user, $userImage){}
                        );
                    }
                    $user->addImage($userImage);
                },
                errorCallback: function ($result) use (
                    $user,
                    &$failedUploads
                ): void {
                    $user->removeImage();
                    $failedUploads[] = $result->originalName;
                },
            );
        }
        catch (\Throwable $exception) {
            $user->removeImage();
            $failedUploads[] = $userImage->originalName;
        }
    }


    private function modifyCompanyLogo(
        Company $company,
        EditFileDto $dto,
        array &$failedUploads,
    ): void {
        $companyLogo = $this->mediaFactory->createStaticMedia(
            $dto->file
        );

        $storageParams =  AccountStorageParams::companyLogo(
            companyId: $company->id()
        );

        try {
            $this->storage->store(
                file: $dto->file,
                storedFileName: $companyLogo->name,
                params: $storageParams,
                successCallback: function ($result) use($company, $storageParams): void {
                    $oldLogo = $company->logo();
                    if($oldLogo){
                        $this->storage->remove(
                            params: $storageParams,
                            fileName: $oldLogo->name,
                            mimeType: $oldLogo->mime,
                            successCallback: function($result){},
                            errorCallback: function(){}
                        );
                    }
                },
                errorCallback: function ($result) use (
                    $company,
                    &$failedUploads
                ): void {
                    $company->setLogo(null);
                    $failedUploads[] = $result->originalName;
                },
            );
            $company->setLogo($companyLogo);
        }
        catch (\Throwable $exception) {
            $company->setLogo(null);
            $failedUploads[] = $companyLogo->originalName;
        }
    }



    private function modifyImages(
        Company $company,
        EditCompanyDto $dto,
        array &$failedUploads,
    ): void {
        $storageParams = AccountStorageParams::companyImages(
            companyId: $company->id(),
        );

        /**
         * ---------------------
         * Adding
         * ---------------------
         */
        $currentIndex = 0;
        foreach ($dto->imagesAdded as $imageDto) {
            if ($imageDto->file === null) {
                continue;
            }

            $companyImage = $this->mediaFactory->createStaticMedia(
                $imageDto->file
            );

            try {
                $this->storage->store(
                    file: $imageDto->file,
                    storedFileName: $companyImage->name,
                    params: $storageParams,
                    successCallback: function ($result) use ($currentIndex, $company, $storageParams): void {
                        $oldImage = $company->images()[$currentIndex];
                        if($oldImage && $oldImage->id){
                            $this->storage->remove(
                                params: $storageParams,
                                fileName: $oldImage->name,
                                mimeType: $oldImage->mime
                            );
                        }
                    },
                    errorCallback: function ($result) use (
                        $company,
                        $companyImage,
                        &$failedUploads
                    ): void {
                        $company->removeImage($companyImage);
                        $failedUploads[] = $result->originalName;
                    },
                );

                if($imageDto->isMain){
                    $company->changeMainImage(
                        $companyImage,
                    );
                }
                else{
                    $company->addImages($companyImage);
                }
            }
            catch (\Throwable $exception) {
                if($imageDto->isMain){
                    $company->changeMainImage(null);
                }
                else{
                    $company->removeImage($companyImage);
                }
                $failedUploads[] = $imageDto->file->getClientOriginalName();
            }

            $currentIndex++;
        }

        /**
         * ---------------------
         * Removal
         * ---------------------
         */
        foreach ($dto->imagesRemoved as $imageId) {
            $image = $company->imageWithId($imageId);
            if ($image === null) continue;
            
            $company->removeImageById($imageId);

            // Puis supprimer du filesystem
            $this->storage->remove(
                params: $storageParams,
                fileName: $image->name,
                mimeType: $image->mime,
                errorCallback: function () use (&$failedUploads, $image): void {
                    // Optionnel : ré-ajouter au domaine si le stockage échoue
                    $failedUploads[] = $image->originalName;
                },
            );
        }
    }
}
