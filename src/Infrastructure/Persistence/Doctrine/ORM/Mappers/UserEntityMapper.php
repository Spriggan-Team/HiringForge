<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Mappers;

use App\Domain\User\User as DomainEntity;
use App\Domain\User\UserId;
use App\Infrastructure\Persistence\Doctrine\ORM\UserEntity as DoctrineEntity;



class UserEntityMapper 
{
    
    public static function toDoctrineEntity(DomainEntity $user): DoctrineEntity
    {
        return  DoctrineEntity::reconstitue(
            id: $user->id()->value(),
            name: $user->name(),
            email: $user->email(),
            password: $user->password(),
            siret: $user->siret(),
            imagePath: $user->imagePath()
        );
    }

    public static function toDomainEntity(DoctrineEntity $doctrineEntity): DomainEntity
    {
        return new DomainEntity(
            id: UserId::fromString($doctrineEntity->getId()),
            name: $doctrineEntity->getName(),
            email: $doctrineEntity->getEmail(),
            password: $doctrineEntity->getPassword(),
            siret:  $doctrineEntity->getSiret(),
            imagePath: $doctrineEntity->getImagePath()
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