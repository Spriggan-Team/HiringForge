<?php

namespace App\Application\Usecase\User;

use App\Application\DTO\User\ChangeUserProfileCommand;
use App\Domain\File\MediaFactoryInterface;
use App\Domain\File\MediaOwnerType;
use App\Domain\File\MediaPurpose;
use App\Domain\File\MediaStorageInterface;
use App\Domain\User\Siret;
use App\Domain\User\UserRepositoryInterface;

class UserModifier
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private MediaStorageInterface $storage,
        private MediaFactoryInterface $mediaFactory
    ) {}

    /**
     * Modify user profile based on command DTO
     * Returns list of failed uploads
     */
    public function execute(ChangeUserProfileCommand $command): array
    {
        $user = $this->repository->findByEmail($command->uuid); // ou findById selon ton design
        $failedUploads = [];

        if ($command->name) {
            $user->rename($command->name);
        }

        if ($command->siret) {
            $user->changeSiret(Siret::create($command->siret));
        }

        if ($command->addImages) {
            foreach ($command->addImages as $image) {
                $staticImage = $this->mediaFactory->createStaticMedia($image);
                $user->addImages($staticImage);

                $this->storage->store(
                    file: $image,
                    ownerId: $user->id(),
                    ownerType: MediaOwnerType::USER,
                    mediaPurpose: MediaPurpose::PROFILE,
                    errorCallback: function($result) use (&$user, $staticImage, &$failedUploads) {
                        $user->removeImage($staticImage);
                        $failedUploads[] = $result->originalName;
                    }
                );
            }
        }

        if ($command->videoPresentation) {
            $timedMedia = $this->mediaFactory->createTimedMedia($command->videoPresentation);
            $user->addVideoPresentation($timedMedia);

            $this->storage->store(
                file: $command->videoPresentation,
                ownerId: $user->id(),
                ownerType: MediaOwnerType::USER,
                mediaPurpose: MediaPurpose::PROFILE,
                errorCallback: function($result) use (&$user, &$failedUploads) {
                    $user->removeVideoPresentation();
                    $failedUploads[] = $result->originalName;
                }
            );
        }

        $this->repository->change($user, $command->uuid, $command->deleteImages);

        return $failedUploads;
    }
}
