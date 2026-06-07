<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\User\Repositories;

use App\Domain\File\StaticMedia;
use App\Domain\Shared\Address;
use App\Domain\Shared\EmailAddress;
use App\Domain\User\Siret;
use App\Domain\User\User as DomainEntity;
use App\Domain\User\UserId;


use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;


class UserEntityMapper 
{
    /**
     * This function trun an existing a user domain entity into a doctrine entity
     * @return DoctrineEntity
     */
public static function toDoctrineEntity(DomainEntity $user): UserEntity
    {
        $address = AddressEntity::create(
            street: $user->address()->street,
            postalCode: $user->address()->postalCode,
            country: $user->address()->country
        );

        $entity = UserEntity::create(
            id: $user->id(),
            name: $user->name(),
            email: $user->email(),
            password: $user->passwordHash(),
            siret: $user->siret(),
            address: $address,
            description: $user->description(),
            logo: new FileEntity()
                        ->setName($user->logo()->name)
                        ->setMime($user->logo()->mime)
                        ->setSize($user->logo()->size)
        );

        $entity->attachToAddress($address);

        foreach ($user->images() as $uploadedImage) {
            $image = new FileEntity();
            $image->setName($uploadedImage->name)
                  ->setMime($uploadedImage->mime)
                  ->setSize($uploadedImage->size);
                  
            $entity->attachToImage($image);
        }

        return $entity;
    }

    /**
     * This function is responsable to transform an existing doctrine entity into a user domain entity
     * @return DomainEntity
     */
    public static function toDomainEntity(UserEntity $doctrine): DomainEntity
    {
        $userImages = [];
        foreach($doctrine->getUserImages() as $userImageEntity){
            $userImages[] = $userImageEntity->image->name;
        }
        $logo = $doctrine->getLogo();

        return DomainEntity::create(
            name: $doctrine->getName(),
            userId: UserId::hydrate($doctrine->getId()),
            
            images: $userImages,
            email: EmailAddress::hydrate($doctrine->getEmail()),
            passwordHash: $doctrine->getPassword(),
            description: $doctrine->getDescription(),

            logo: $logo ? StaticMedia::hydrate(
                name: $logo->getName(),
                size: $logo->getSize(),
                mime: $logo->getMime(),
            ) : null,
            siret:  Siret::hydrate($doctrine->getSiret()),

            address:  Address::hydrate(
                street: $doctrine->getAddress()->getStreet(),
                postalCode: $doctrine->getAddress()->getPostalCode(),
                country: $doctrine->getAddress()->getCountry()
            )
        );
    }

    /**
     * This is responsable to copy/update an User using the domain entity
     * @return void
     */
    public static function copy(DomainEntity $user, UserEntity $entity): void
    {
        $entity->setName($user->name())
               ->setEmail($user->email())
               ->setPassword($user->passwordHash())
               ->setSiret($user->siret())
               ->setDescription($user->description());
    }
}
