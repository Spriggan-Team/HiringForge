<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\User\Repositories;

use App\Api\Responder\ApiResponse;
use App\Domain\File\StaticMedia;
use App\Domain\Shared\EmailAddress;
use App\Domain\User\User as DomainEntity;

use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;


class UserEntityMapper 
{
    /**
     * This function trun an existing a user domain entity into a doctrine entity
     * @return DoctrineEntity
     */
    public static function toDoctrineEntity(
        DomainEntity $user, 
        CompanyEntity $companyProxy, 
    ): UserEntity {
        $entity = UserEntity::create(
            id: $user->id(),
            email: $user->email(),
            firstName: $user->firstName(),
            lastName: $user->lastName(),
            password: $user->passwordHash(),
            description: $user->description(),
            company: $companyProxy,     // Reçu depuis le repository
            userRole: $user->role(),        // Reçu depuis le repository
        );

        $image = null;
        if($user->image()){
            $img = $user->image();
            $image =  FileEntity::create(name: $img->name, mime: $img->mime, size: $img->size, originalName: $img->originalName);
        }

        $entity->setImage($image);

        return $entity;
    }

    /**
     * This function is responsable to transform an existing doctrine entity into a user domain entity
     * @return DomainEntity
     */
    public static function toDomainEntity(UserEntity $doctrine): DomainEntity
    {
        $image = null;
        if($doctrine->getImage()){
            $image = new StaticMedia(
                name: $doctrine->getImage()->getName(),
                size: $doctrine->getImage()->getSize(),
                mime: $doctrine->getImage()->getMime(),
                originalName: $doctrine->getImage()->getOriginalName()
            );
        }
       
        return DomainEntity::create(
            firstName: $doctrine->getFirstName(),
            lastName: $doctrine->getLastName(),
            image: $image,
            email: EmailAddress::hydrate($doctrine->getEmail()),
            passwordHash: $doctrine->getPassword(),
            description: $doctrine->getDescription(),
            companyId: $doctrine->getCompany()->getId(),
            role: $doctrine->getUserRole()
        );
    }

    /**
     * This is responsable to copy/update an User using the domain entity
     * @return void
     */
    public static function copy(DomainEntity $user, UserEntity $entity): void
    {
        $image = null;
        if($entity->getImage()){
            $image = new StaticMedia(
                name: $entity->getImage()->getName(),
                size: $entity->getImage()->getSize(),
                mime: $entity->getImage()->getMime(),
                originalName: $entity->getImage()->getOriginalName()
            );
        }

        $entity->setFirstName($user->firstName())
               ->setLastName($user->lastName())
               ->setEmail($user->email())
               ->setImage($image)
               ->setPassword($user->passwordHash())
               ->setDescription($user->description());
    }
}
