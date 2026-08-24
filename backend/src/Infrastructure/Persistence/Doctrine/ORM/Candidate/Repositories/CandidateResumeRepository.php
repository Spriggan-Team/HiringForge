<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate\Repositories;

use App\Domain\Candidate\CandidateResume;
use App\Domain\Candidate\CandidateResumeRepositoryInterface;
use App\Domain\File\StaticMedia;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateResumeEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\Mapper\FileEntityMapper;
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
    public function countResume(string $candidateId): int
    {
        return parent::count([
            "candidate" => $candidateId
        ]);
    }



    #[Override]
    public function getResumeFiles(string $candidateId): array
    {
        throw new \Exception('Not implemented');
    }

    #[Override]
    public function findOneOrNull(string $candidateId, string $fileId): ?CandidateResume
    {
        /**
         * @var CandidateResumeEntity|null $resume
         */
        $resume = $this->findOneBy([
            "candidate" => $candidateId,
            "fileId" => $fileId
        ]);

        if(!$resume){
            return null;
        }

        return  CandidateResume::hydrate(
            id: $resume->getId(),
            fileId: $fileId,
            candidateId: $candidateId
        );
    }


    #[Override]
    public function findResumeMediaOrNull(string $candidateId, string $fileId): ?StaticMedia
    {
        /**
         * @var CandidateResumeEntity|null $resume
         */
        $resume = $this->findOneBy([
            "candidate" => $candidateId,
            "file" => $fileId
        ]);

        if(!$resume){
            return null;
        }

        return FileEntityMapper::toStaticDomainMedia(file: $resume->getFile());
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

        //-- Entities references
        $candidate = $em->find(CandidateEntity::class, $candidateResume->candidateId());
        $resume = $em->find(FileEntity::class, $candidateResume->fileId());

        if (!$candidate || !$resume) {
            throw new \RuntimeException("Candidate or File entity not found in memory.");
        }

        $entity = CandidateResumeEntity::create(
            candidate: $candidate,
            file: $resume
        );

        $em->persist($entity);
        $em->flush();

        return (string) $entity->getId();
    }


    #[Override]
    public function delete(string $candidateId, string $fileId): void
    {
        $entity = $this->findOneBy([
            "file" => $fileId,
            "candidate" => $candidateId
        ]);
        if($entity){
            $em = $this->getEntityManager();
            $em->remove($entity);
            $em->flush();
        }
    }
}