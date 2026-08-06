<?php

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

    public function toEntity(Offer $offer) : OfferEntity {
        //-- Doctrine Reference
        /** @var ApplicationEntity $applicationRef */
        $applicationRef = $this->em->getReference(ApplicationEntity::class, $offer->applicationId());

        /** @var CandidateEntity $candidateRef */
        $candidateRef = $this->em->getReference(CandidateEntity::class, $offer->candidateId());

        // Mapping Domain\Offer -> OfferEntity
        $entity = new OfferEntity();
        
        if ($offer->title() !== null) {
            $entity->setTitle($offer->title());
        }
        
        if ($offer->message() !== null) {
            $entity->setMessage($offer->message());
        }
        
        if ($offer->salary() !== null) {
            $entity->setSalary($offer->salary());
        }

        $entity->setStatus($offer->status())
            ->setExpiredDate($offer->expiredAt())
            ->setApplication($applicationRef)
            ->setCandidate($candidateRef);
        return $entity;
    }
}