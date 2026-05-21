<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Repositories;

use App\Domain\JobOffer\JobOffer as DomainEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;

use DateTimeImmutable;

/**
 * This is used for transmitting job offer domain object into its corresponding doctrine entity object & 
 * inversly...
 */
class JobOfferEntityMapper
{

    public static function toDomain(JobOfferEntity $entity): DomainEntity
    {
        return  DomainEntity::create(
            id: $entity->getId(),
            title: $entity->getTitle(),
            content: $entity->getContent(),
            status: $entity->getStatus()
        );
    }

    public static function toDoctrine(DomainEntity $jobOfferDomain, UserEntity $user): JobOfferEntity
    {
        return  JobOfferEntity::reconstitue(
            id: $jobOfferDomain->id(),
            title: $jobOfferDomain->title(),
            content: $jobOfferDomain->content(),
            createdAt: $jobOfferDomain->createdAt(),
            updatedAt: $jobOfferDomain->updatedAt(),
            user: $user, status: $jobOfferDomain->status()
        );
    }

    public static function copy(DomainEntity $offer, JobOfferEntity $doctrine)
    {
        $doctrine->setTitle($offer->title())
                 ->setContent($offer->content())
                 ->setStatus($offer->status())
                 ->setUpdatedAt(new DateTimeImmutable());
    }
}