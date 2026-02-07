<?php

namespace App\Application\Command\Usecase\User;

use App\Domain\File\TimedMedia;
use App\Domain\File\FilePurpose;
use App\Domain\File\FileOwnerType;
use App\Domain\File\TimedMediaType;
use App\Domain\File\FileStorageInterface;

use App\Domain\User\Siret;
use App\Domain\User\UserRepositoryInterface;

/**
 * Deal with patch request directed toward account 
 */

class UserModifier
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private FileStorageInterface $storage,
    ){}

    /**
     * This function is used to change information about an user
     */
    public function execute(
        string $uuid,
        ?string $name,
        ?string $siret,
        /** @var \App\Domain\File\StaticMedia */
        array $addImages,
        /** @var array<string> */
        array $deleteImages ,
        /** @var mixed this one the the file type you decide to handle in your infrastructure  */
        ?TimedMedia $presentation = null
    ): void
    {
        $user = $this->repository->findByEmail($uuid);
        
        if($name){
            $user->rename($name);
        }

        if($siret){
            $user->changeSiret(new Siret($siret));
        }

        if(count($addImages) > 0){
            foreach($addImages as $staticImage){
                $user->addImages($staticImage);
                $this->storage->store(
                    file: $staticImage,
                    userId: $user->id(),
                    ownerType: FileOwnerType::USER,
                    purpose: FilePurpose::PROFILE
                );
            }
        }

        if(count($deleteImages)){
            foreach($deleteImages as $uniqName){
                $user->removeImage($uniqName);
            }
        }

        if($presentation){
            $presentation->mustBe(TimedMediaType::VIDEO);
            $user->setPresentation($presentation);
            $this->storage->store(
                file: $presentation,
                userId: $user->id(),
                ownerType: FileOwnerType::USER,
                purpose: FilePurpose::PROFILE
            );
        }

        $this->repository->change($user, $uuid);
    }
}