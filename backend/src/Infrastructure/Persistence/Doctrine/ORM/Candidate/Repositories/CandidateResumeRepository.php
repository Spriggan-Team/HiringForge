<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate\Repositories;

use App\Domain\Candidate\CandidateResume;
use App\Domain\Candidate\CandidateResumeRepositoryInterface;
use App\Domain\File\StaticMedia;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateResumeEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


use Override;



class CandidateResumeRepository extends ServiceEntityRepository
    implements CandidateResumeRepositoryInterface
 
{
    public function __construct(
        private ManagerRegistry $manager
    )
    {
        parent::__construct($manager, CandidateResumeEntity::class);
    }

    
    #[Override]
    public function getResumeFile(string $candidateId): ?StaticMedia
    {
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function exists(string $candidateId, float $fileId): bool
    {
        return $this->createQueryBuilder('cr')
            ->select('1')
            ->where('IDENTITY(cr.candidate) = :candidateId')
            ->andWhere('IDENTITY(cr.file) = :fileId')
            ->setParameter('candidateId', $candidateId)
            ->setParameter('fileId', $fileId)
            ->getQuery()
            ->getOneOrNullResult() !== null;
    }

    #[Override]
    public function findCandidateResumeIdByFileId(string $fileId): ?string
    {
        $result = $this->createQueryBuilder('cr')
            ->select('cr.id')
            ->where('cr.file = :fileId')
            ->setParameter('fileId', $fileId)
            ->getQuery()
            ->getOneOrNullResult();

        return $result['id'] ?? null;
    }


    #[Override]
    public function markAsParsed(string $candidateResumeId): void
    {
        $resume = $this->find($candidateResumeId);

        if ($resume === null) {
            throw new \DomainException(
                'Candidate resume not found.'
            );
        }

        $resume->markAsParsed();

        $this->entityManager->flush();
    }
    

    
    #[Override]
    public function hasBeenAnalyzed(
        string $candidateId,
        string $fileId
    ): bool {
        $result = $this->createQueryBuilder('cr')
            ->select('1')
            ->where('cr.candidate = :candidateId')
            ->andWhere('cr.file = :fileId')
            ->andWhere('cr.hasbeenAnalyzed = :analyzed')
            ->setParameter('candidateId', $candidateId)
            ->setParameter('fileId', $fileId)
            ->setParameter('analyzed', true)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result !== null;
    }


    #[Override]
    public function save(CandidateResume $candidateResume): string
    {
        $em = $this->getEntityManager();

        $candidate = $em->getReference(
            CandidateEntity::class,
            $candidateResume->candidateId()
        );

        $resume = $em->getReference(
            FileEntity::class,
            $candidateResume->fileId()
        );

        $entity = CandidateResumeEntity::create(
            candidate: $candidate,
            file: $resume
        );

        $em->persist($entity);
        $em->flush();

        return $entity->getId();
    }
}