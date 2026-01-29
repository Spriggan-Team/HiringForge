<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\User;

use App\Domain\Shared\ValueObject\Address;
use App\Domain\User\User as DomainEntity;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Image\ImageEntity;

class UserEntityMapper 
{
    /**
     * This function trun an existing a user domain entity into a doctrine entity
     * @return DoctrineEntity
     */
    public static function toDoctrineEntity(DomainEntity $user): UserEntity
    {
        $entity = UserEntity::create(
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

        $entity->attachToAddress($address);
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
    public static function copy(DomainEntity $user, UserEntity $entity): void
    {
        $entity->setName($user->name())
               ->setEmail($user->email())
               ->setPassword($user->password())
               ->setSiret($user->siret());
    }
}
