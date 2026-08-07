<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Offer\Repositories;

use App\Domain\Offer\Offer;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Offer\OfferEntity;


use Doctrine\ORM\EntityManagerInterface;


class OfferRepositoryMapper
{
    public function __construct(
        private EntityManagerInterface $em
    ){}

    public function toEntity(Offer $offer, ?OfferEntity $existingEntity = null): OfferEntity
    {
        if ($existingEntity === null) {
            // --- CREATION (Instantiation + Initialization Fields) ---
            $entity = new OfferEntity();
            
            // Fixed ownership at the time of creation
            $entity->setSentAt($offer->sentAt());

            // Relations immuables définies uniquement à la création
            if ($offer->candidateId()) {
                $candidateRef = $this->em->getReference(CandidateEntity::class, $offer->candidateId());
                $entity->setCandidate($candidateRef);
            }

            if ($offer->applicationId()) {
                $applicationRef = $this->em->getReference(ApplicationEntity::class, $offer->applicationId());
                $entity->setApplication($applicationRef);
            }
        }
        else {
            // --- Update ---
            $entity = $existingEntity;
            // On NE touche PAS à sentAt, candidate ni application ici !
        }

        // --- MUTABLE FIELDS (Create and Update) ---
        $entity->setTitle($offer->title());
        $entity->setMessage($offer->message());
        $entity->setSalary($offer->salary());
        $entity->setStatus($offer->status());
        $entity->setExpiredAt($offer->expiredAt());

        return $entity;
    }
}