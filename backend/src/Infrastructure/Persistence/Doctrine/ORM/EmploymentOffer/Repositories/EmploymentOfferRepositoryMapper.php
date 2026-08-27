<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\EmploymentOffer\Repositories;

use App\Domain\EmploymentOffer\EmploymentOffer;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\EmploymentOffer\EmploymentOfferEntity;

use Doctrine\ORM\EntityManagerInterface;


class EmploymentOfferRepositoryMapper
{
    public function __construct(
        private EntityManagerInterface $em
    ){}

    public function toDomain(EmploymentOfferEntity $entity): EmploymentOffer
    {
        return EmploymentOffer::hydrate(
            id: $entity->getId(),
            candidateId: $entity->getCandidate()?->getId(),
            applicationId: $entity->getApplication()?->getId(),
            message: $entity->getMessage(),
            salary: $entity->getSalary(),
            status: $entity->getStatus(),
            expiredAt: $entity->getExpiredAt(),
            createdAt: $entity->getCreatedAt(),
            rejectionReason: $entity->getRejectionReason()
        );
    }

    public function toEntity(EmploymentOffer $domain, ?EmploymentOfferEntity $existingEntity = null): EmploymentOfferEntity
    {
        if ($existingEntity === null) {
            // --- CREATION (Instantiation + Initialization Fields) ---
            $entity = new EmploymentOfferEntity();
            

            // Fixed relationships defined only at creation
            if ($domain->candidateId()) {
                $candidateRef = $this->em->getReference(CandidateEntity::class, $domain->candidateId());
                $entity->setCandidate($candidateRef);
            }

            if ($domain->applicationId()) {
                $applicationRef = $this->em->getReference(ApplicationEntity::class, $domain->applicationId());
                $entity->setApplication($applicationRef);
            }
        }
        else {
            // --- Update ---
            $entity = $existingEntity;
        }

        // --- MUTABLE FIELDS (Create and Update) ---

        $entity->setMessage($domain->message());
        $entity->setSalary($domain->salary());
        $entity->setStatus($domain->status());
        $entity->setExpiredAt($domain->expiredAt());
        $entity->setRejectionReason($domain->rejectionReason());

        return $entity;
    }
}