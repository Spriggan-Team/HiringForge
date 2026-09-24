<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Interview\Repositories\Mapper;

use App\Domain\Interviews\Interview;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity ;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;


use Doctrine\ORM\EntityManagerInterface;


class InterviewEntityMapper
{
    public function __construct(
        private EntityManagerInterface $em
    )
    {}

    /**
     * Domain -> Doctrine Entity
     */
    public function toEntity(
        Interview $interview,
        ?InterviewEntity $entity = null
    ): InterviewEntity {
        $application = $this->em->getReference(
            ApplicationEntity::class,
            $interview->getApplicationId()
        );

        $user = $this->em->getReference(
            UserEntity::class,
            $interview->getUserId()
        );

        if ($entity === null) {
            return InterviewEntity::create(
                id: $interview->getId(),
                startDate: $interview->getStartDate(),
                minutes: $interview->getMinutes(),
                description: $interview->getDescription() ?? '',
                application: $application,
                user: $user,
                title: $interview->getTitle(),
                url: $interview->getUrl(),
                status: $interview->getStatus(),
                type: $interview->getType(),
            );
        }

        // Update entity existante
        $entity->setStartDate($interview->getStartDate());
        $entity->setDuration($interview->getMinutes());
        $entity->setTitle($interview->getTitle());
        $entity->setDescription($interview->getDescription() ?? '');
        $entity->setStatus($interview->getStatus());
        $entity->setUrl($interview->getUrl());
        $entity->setType($interview->getType());
        $entity->setApplication($application);
        $entity->setUser($user);

        return $entity;
    }


    /**
     * Entity -> Domain
     */
    public function toDomain(
        InterviewEntity $entity
    ): Interview {
        return Interview::reconstitute(
            id: $entity->getId(),
            minutes: $entity->getMinutes(),
            startDate: $entity->getStartDate(),
            applicationId: $entity->getApplication()->getId(),
            userId: $entity->getUser()->getId(),
            status: $entity->getStatus(),
            title: $entity->getTitle(),
            description: $entity->getDescription(),
            url: $entity->getURL(),
            type: $entity->getType(),
            candidateApproval: $entity->getCandidateApproval(),
            rejectionReason: $entity->getRejectionReason(),
        );
    }


    /**
     * Update an existing Doctrine entity
     */
    private function updateEntity(
        InterviewEntity $entity,
        Interview $interview,
        ApplicationEntity $application,
        UserEntity $user
    ): InterviewEntity {
        $entity
            ->setStartDate($interview->getStartDate())
            ->setDuration($interview->getMinutes())
            ->setTitle($interview->getTitle())
            ->setdescription($interview->getDescription())
            ->setUrl($interview->getUrl())
            ->setStatus($interview->getStatus())
            ->setType($interview->getType())
            ->setApplication($application)
            ->setUser($user)
            ->setCandidateApproval(
                $interview->isCandidateApproved()
            )
            ->setRejectionReason(
                $interview->getRejectionReason()
            );

        return $entity;
    }
}