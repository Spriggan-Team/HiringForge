<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Mapper;


use App\Domain\ValueObject\MergeRule;
use App\Domain\Entity\Post as PostDomainEntity;

use App\Infrastructure\Persistence\Doctrine\ORM\PostEntity as PostDoctrineEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\AccountEntity  as AccountDoctrineEntity; 


class PostEntityMapper{

    public static function toDomainEntity(PostDoctrineEntity $entity): PostDomainEntity
    {
        return new PostDomainEntity(
            id: $entity->getId(),
            title: $entity->getTitle(),
            content: $entity->getContent(),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt()
        );
    }

    public static function toDoctrineEntity(PostDomainEntity $domainEntity, AccountDoctrineEntity $account): PostDoctrineEntity
    {
        return  PostDoctrineEntity::create(
            id: $domainEntity->getId(),
            title: $domainEntity->getTitle(),
            content: $domainEntity->getContent(),
            createdAt: $domainEntity->getCreatedAt(),
            updatedAt: $domainEntity->getUpdatedAt(),
            account: $account
        );
    }

    public static function mergeIntoDoctrineEntity(
        PostDomainEntity $domain,
        PostDoctrineEntity $postdoctrine,
        AccountDoctrineEntity $account,
        MergeRule $rule,
    ):void
    {
        match($rule){
            MergeRule::FULL_OVERWRITE => self::fullMerge($domain, $postdoctrine, $account),
            MergeRule::PARTIAL_MERGE  => self::partialMerge($domain, $postdoctrine, $account),
        };
    }

    public static function fullMerge(PostDomainEntity $domain, PostDoctrineEntity  $doctrine,  AccountDoctrineEntity $account):void
    {
        $doctrine->setTitle($domain->getTitle());
        $doctrine->setContent($domain->getContent());
        $doctrine->setCreatedAt($domain->getCreatedAt());
        $doctrine->setUpdatedAt($domain->getUpdatedAt());
        $doctrine->setUpdatedAt($domain->getUpdatedAt());
        $doctrine->setAccount($account);
    }
    
    public static function partialMerge(PostDomainEntity $domain, PostDoctrineEntity  $doctrine,  AccountDoctrineEntity $account):void
    {
        if($domain->hasChanged("title")){
            $doctrine->setTitle($domain->getTitle());
        }
        if($domain->hasChanged("content")){
            $doctrine->setContent($domain->getContent());
        }
        $doctrine->setUpdatedAt(new \DateTimeImmutable());
    }

}