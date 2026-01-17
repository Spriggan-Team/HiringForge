<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Mappers;


use App\Domain\JobOffer\JobOffer as DomainEntity;

use App\Infrastructure\Persistence\Doctrine\ORM\JobOfferEntity as JobOfferDoctrineEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\UserEntity  as UserDoctrineEntity;
use DateTimeImmutable;

class JobOfferEntityMapper
{

    public static function toDomain(JobOfferDoctrineEntity $entity): DomainEntity
    {
        return new DomainEntity(
            id: $entity->getId(),
            title: $entity->getTitle(),
            content: $entity->getContent(),
            createdAt: $entity->getCreatedAt(),
            updatedAt: $entity->getUpdatedAt(),
            isPublished: $entity->getIsPublished()
        );
    }

    public static function toDoctrine(DomainEntity $jobOfferDomain, UserDoctrineEntity $user): JobOfferDoctrineEntity
    {
        return  JobOfferDoctrineEntity::reconstitue(
            id: $jobOfferDomain->id(),
            title: $jobOfferDomain->title(),
            content: $jobOfferDomain->content(),
            createdAt: $jobOfferDomain->createdAt(),
            updatedAt: $jobOfferDomain->updatedAt(),
            user: $user, isPublished: $jobOfferDomain->isPublished()
        );
    }

    public static function copy(DomainEntity $offer, JobOfferDoctrineEntity $doctrine)
    {
        $doctrine->setTitle($offer->title())
                 ->setContent($offer->content())
                 ->setIsPulished($offer->isPublished())
                 ->setUpdatedAt(new DateTimeImmutable());
    }
}