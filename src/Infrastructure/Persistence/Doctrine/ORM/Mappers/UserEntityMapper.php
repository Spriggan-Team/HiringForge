<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Mappers;

use App\Domain\User\UserId;
use App\Domain\Shared\ValueObject\Address;
use App\Domain\User\User as DomainEntity;

use App\Infrastructure\Persistence\Doctrine\ORM\AddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\UserEntity as DoctrineEntity;



class UserEntityMapper 
{
    
    public static function toDoctrineEntity(DomainEntity $user): DoctrineEntity
    {
        $entity = DoctrineEntity::reconstitue(
            id: $user->id()->value(),
            name: $user->name(),
            email: $user->email(),
            password: $user->password(),
            siret: $user->siret(),
            imagePath: $user->imagePath(),
            address: new AddressEntity()
        );

        $address = AddressEntity::reconstitue(
            city: $user->address()->city,
            street: $user->address()->street,
            postalCode: $user->address()->postalCode,
            country: $user->address()->country
        );

        $address->setUser($entity);
        $entity->setAddress($address);

        return $entity;
    }


    public static function toDomainEntity(DoctrineEntity $doctrine): DomainEntity
    {
        return  DomainEntity::create(
            id: UserId::fromString($doctrine->getId()),
            name: $doctrine->getName(),
            email: $doctrine->getEmail(),
            password: $doctrine->getPassword(),
            siret:  $doctrine->getSiret(),
            imagePath: $doctrine->getImagePath(),
            address: new Address(
                street: $doctrine->getAddress()->getStreet(),
                city: $doctrine->getAddress()->getCity(),
                postalCode: $doctrine->getAddress()->getPostalCode(),
                country: $doctrine->getAddress()->getCountry()
            )
        );
    }

    public static function copy(DomainEntity $user, DoctrineEntity  $entity ):void
    {
        $entity->setName($user->name())
               ->setEmail($user->email())
               ->setImagePath($user->imagePath())
               ->setPassword($user->password());
    }

}