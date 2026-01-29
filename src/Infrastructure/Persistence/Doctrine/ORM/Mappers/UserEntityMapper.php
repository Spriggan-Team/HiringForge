<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Mappers;

use App\Domain\User\UserId;
use App\Domain\Shared\ValueObject\Address;
use App\Domain\User\User as DomainEntity;

use App\Infrastructure\Persistence\Doctrine\ORM\AddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\ImageEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\UserEntity as DoctrineEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\UserImageEntity;

class UserEntityMapper 
{
    /**
     * This function trun an existing a user domain entity into a doctrine entity
     * @return array [DoctrineEntity, array<UserImageEntity>]
     */
    public static function toDoctrineEntity(DomainEntity $user): DoctrineEntity
    {
        $entity = DoctrineEntity::create(
            id: $user->id(),
            name: $user->name(),
            email: $user->email(),
            password: $user->password(),
            siret: $user->siret(),
            address: new AddressEntity()
        );

        $address = AddressEntity::create(
            city: $user->address()->city,
            street: $user->address()->street,
            postalCode: $user->address()->postalCode,
            country: $user->address()->country
        );

        //Insert user image collection
        foreach($user->images() as $uploadedImage){
           $image   = new ImageEntity()
                            ->setId($uploadedImage->id ?? null)
                            ->setMime($uploadedImage->mime)
                            ->setSize($uploadedImage->size);
            $entity->attachToImage($image);
        }

        $address->attachToUser($entity);
        $entity->attachToAddress($address);
        return $entity;
    }


    /**
     * This function is responsable to transform an existing doctrine entity into a user domain entity
     * @return DomainEntity
     */
    public static function toDomainEntity(DoctrineEntity $doctrine): DomainEntity
    {
        $userImages = [];
        foreach($doctrine->getUserImages() as $userImageEntity){
            $userImages[] = $userImageEntity->image->originalName;
        }
        return DomainEntity::create(
            name: $doctrine->getName(),
            email: $doctrine->getEmail(),
            images: $userImages,
            password: $doctrine->getPassword(),
            siret: $doctrine->getSiret(),
            address: new Address(
                street: $doctrine->getAddress()->getStreet(),
                city: $doctrine->getAddress()->getCity(),
                postalCode: $doctrine->getAddress()->getPostalCode(),
                country: $doctrine->getAddress()->getCountry()
            )
        );
    }

    /**
     * This is responsable to copy/update an User using the domain entity
     * @return void
     */
    public static function copy(DomainEntity $user, DoctrineEntity $entity): void
    {
        $entity->setName($user->name())
               ->setEmail($user->email())
               ->setPassword($user->password())
               ->setSiret($user->siret());
    }
}
