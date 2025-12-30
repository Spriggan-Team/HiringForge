<?php


namespace App\Infrastructure\Persistence\MySQL\Mapper;


use App\Domain\Entity\Account as DomainEntity;
use App\Domain\ValueObject\MergeRule;
use App\Infrastructure\Persistence\MySQL\Doctrine\AccountEntity as DoctrineEntity;



class AccountEntityMapper{
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

    public static function toDomainEntity(DoctrineEntity $doctrineAntity): DomainEntity
    {
        return new DomainEntity(
            id: $doctrineAntity->getId(),
            name: $doctrineAntity->getName(),
            email: $doctrineAntity->getEmail(),
            password: $doctrineAntity->getPassword(),
            siret:  $doctrineAntity->getSiret(),
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

    public static function partialMerge(DomainEntity $account, DoctrineEntity  $entity)
    {
        if($account->hasChanged("email")){
            $entity->setEmail($account->getEmail());
        }
        if($account->hasChanged("password")){
            $entity->setPassword( $account->getPassword());
        }
    }

    public static function fullMerge(DomainEntity $account, DoctrineEntity  $entity)
    {
        $entity->setName($account->getName());
        $entity->setEmail($account->getEmail());
        $entity->setPassword($account->getPassword());
        $entity->setSiret($account->getSiret());
    }

}