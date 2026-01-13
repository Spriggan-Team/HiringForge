<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Mapper;

use App\Domain\ValueObject\MergeRule;
use App\Domain\Entity\User as DomainEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\UserEntity as DoctrineEntity;



class UserEntityMapper 
{
    
    public static function toDoctrineEntity(DomainEntity $account): DoctrineEntity
    {
        return  DoctrineEntity::create(
            id: $account->getId(),
            name: $account->getName(),
            email: $account->getEmail(),
            password: $account->getPassword(),
            siret: $account->getSiret()
        );
    }

    public static function toDomainEntity(DoctrineEntity $doctrineEntity): DomainEntity
    {
        return new DomainEntity(
            id: $doctrineEntity->getId(),
            name: $doctrineEntity->getName(),
            email: $doctrineEntity->getEmail(),
            password: $doctrineEntity->getPassword(),
            siret:  $doctrineEntity->getSiret(),
        );
    }

    public static function mergeIntoDoctrineEntity(DomainEntity $account, DoctrineEntity  $entity, MergeRule $rule):void
    {
        match($rule){
            MergeRule::FULL_OVERWRITE => self::fullMerge($account, $entity),
            MergeRule::PARTIAL_MERGE  => self::partialMerge($account, $entity),
        };
    }

    //-----------Mergers

    public static function fullMerge(DomainEntity $account, DoctrineEntity  $entity): void
    {
        $entity->setName($account->getName());
        $entity->setEmail($account->getEmail());
        $entity->setPassword($account->getPassword());
        $entity->setSiret($account->getSiret());
    }

    public static function partialMerge(DomainEntity $account, DoctrineEntity  $entity): void
    {
        if($account->hasChanged("email")){
            $entity->setEmail($account->getEmail());
        }
        if($account->hasChanged("password")){
            $entity->setPassword( $account->getPassword());
        }
    }



}