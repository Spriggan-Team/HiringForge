<?php

namespace App\Application\Command\Usecase\User;

use App\Domain\File\MediaFactoryInterface;
use App\Domain\File\MediaOwnerType;
use App\Domain\File\MediaPurpose;
use App\Domain\File\MediaStorageInterface;
use App\Domain\User\Siret;

use App\Domain\User\UserRepositoryInterface;

/**
 * Deal with patch request directed toward account 
 */

class UserModifier
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private MediaStorageInterface $storage,
        private MediaFactoryInterface $mediaFactory
    ){}

    /**
     * This function is used to change information about an user
     * @return array an array of the failed upload (filename)
     */
    public function execute(
        string $uuid,
        ?string $name,
        ?string $siret,

        /** 
         * @var mixed[]  $addImages - contains all the file to be registered as image.
         *                            The type/class of file should be what you handle in the infrastructure
        */
        array $addImages,

        /** 
         * @var string[] $deleteImages - contains all the image that shoul be deleted
        */
        array $deleteImages,

        /** 
         * @var mixed $videoPresentation -   this one is the the file you want to record as presentation;
         *                              the type (class) of file you pass should be of what you decide to handle in your infrastructure, specially
         *                              what you handle in  MediaStorageInterface & MediaFactoryInterface (implements...)
         * 
        */
        mixed $videoPresentation = null
    ): array
    {
        $user = $this->repository->findByEmail($uuid);
        
        if($name){
            $user->rename($name);
        }

        if($siret){
            $user->changeSiret(Siret::create($siret));
        }

        /** @var string[] */
        $failedUploads = [];
        
        if(count($addImages) > 0){
            foreach($addImages as $image){
                $staticImage = $this->mediaFactory->createStaticMedia($image);
                $user->addImages($staticImage);

                $this->storage->store(
                    file: $image,
                    ownerId: $user->id(),
                    ownerType: MediaOwnerType::USER,
                    mediaPurpose: MediaPurpose::PROFILE,
                    errorCallback: function($result) use(&$user, $staticImage, &$failedUploads){
                        $user->removeImage($staticImage);
                        $failedUploads = $result->originalName;
                    }
                );
            }
        }


        if($videoPresentation){
            $timedMedia = $this->mediaFactory->createTimedMedia($videoPresentation);
            $user->addVideoPresentation($timedMedia); 
            $this->storage->store(
                file: $videoPresentation,
                ownerId: $user->id(),
                ownerType: MediaOwnerType::USER,
                mediaPurpose: MediaPurpose::PROFILE,
                successCallback: function ($result) use (&$user, &$failedUploads){
                    $user->removeVideoPresentation();
                    $failedUploads[] = $result->originalName;
                }
            );
        }

        $this->repository->change($user, $uuid, $deleteImages);

        return $failedUploads;
    }
}