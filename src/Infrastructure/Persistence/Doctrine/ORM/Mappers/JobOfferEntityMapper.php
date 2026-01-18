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
        return  DomainEntity::create(
            id: $entity->getId(),
            title: $entity->getTitle(),
            content: $entity->getContent(),
            status: $entity->getStatus()
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
            user: $user, status: $jobOfferDomain->status()
        );
    }

    public static function copy(DomainEntity $offer, JobOfferDoctrineEntity $doctrine)
    {
        $doctrine->setTitle($offer->title())
                 ->setContent($offer->content())
                 ->setStatus($offer->status())
                 ->setUpdatedAt(new DateTimeImmutable());
    }
}