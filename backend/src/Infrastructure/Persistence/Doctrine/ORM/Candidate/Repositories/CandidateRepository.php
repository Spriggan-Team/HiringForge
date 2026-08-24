<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate\Repositories;

use App\Api\Responder\ApiResponse;
use App\Domain\File\StaticMedia;
use App\Domain\Candidate\Candidate;
use App\Domain\Candidate\CandidateLightModel;
use App\Domain\Exception\RessourceNotFound;

use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Exception\UnauthorizedAction;
use App\Domain\Shared\Address;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateResumeEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\Mapper\FileEntityMapper;


use Doctrine\ORM\EntityManagerInterface;


use Override;


class CandidateRepository implements CandidateRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private FileEntityMapper $fileMapper,
        private CandidateEntityMapper $mapper
    ){}


    #[Override]
    public function getDescription(string $candidateId): string
    {
        $result = $this->em->createQueryBuilder()
            ->select('c.description')
            ->from(CandidateEntity::class, 'c')
            ->where('c.id = :candidateId')
            ->setParameter('candidateId', $candidateId)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result === null) {
            throw new \DomainException(
                'Candidate not found.'
            );
        }

        return (string) $result['description'];
    }

    
    #[Override]
    public function getCandidateApplicationJobIds(string $candidateId): array
    {
        return $this->em->createQueryBuilder()
            ->select('j.id')
            ->from(ApplicationEntity::class, 'a')
            ->leftJoin('a.jobOffer', 'j')
            ->where('a.candidate = :candidateId')
            ->setParameter('candidateId', $candidateId)
            ->getQuery()
            ->getSingleColumnResult();
    }


    #[Override]
    public function getFullName(string $candidateId): string
    {
        $result = $this->em->createQueryBuilder()
            ->select("CONCAT(c.firstName, ' ', c.lastName)")
            ->from(CandidateEntity::class, 'c')
            ->where('c.id = :candidateId')
            ->setParameter('candidateId', $candidateId)
            ->getQuery()
            ->getOneOrNullResult();

        if ($result === null) {
            throw new \DomainException(
                sprintf('Candidate "%s" was not found.', $candidateId)
            );
        }

        return trim((string) $result[1]);
    }



    #[Override]
    public function exists(string $candidateId): bool
    {
        $count = $this->em
            ->getRepository(CandidateEntity::class)
            ->count([
                'id' => $candidateId,
            ]);

        return $count > 0;
    }


    /** returns applications ids of an candidate */
    #[Override]
    public function getApplicationsIds(string $candidateId): array
    {
        return array_column(
            $this->em->createQueryBuilder()
                ->select('a.id')
                ->from(ApplicationEntity::class, 'a')
                ->where('a.candidate = :candidateId')
                ->setParameter('candidateId', $candidateId)
                ->getQuery()
                ->getScalarResult(),
            'id'
        );
    }
    

    #[Override]
    public function canApply(string $candidateId, string $jobId): bool
    {
        $result = $this->em->createQueryBuilder()
            ->select('1')
            ->from(ApplicationEntity::class, 'a')
            ->where('a.candidate = :candidateId')
            ->andWhere('a.jobOffer = :jobId')
            ->setParameter('candidateId', $candidateId)
            ->setParameter('jobId', $jobId)
            ->setMaxResults(1)
            ->getQuery()
            ->getScalarResult();

        return $result === [];
    }



    #[Override]
    public function assertResumeBelongsToCandidate(
        string $candidateId,
        string $fileId
    ): void {
        $repository = $this->em->getRepository(CandidateResumeEntity::class);

        $found = $repository->findOneBy([
            'candidate' => $candidateId,
            'file' => $fileId,
        ]);

        if (!$found) {
            throw new UnauthorizedAction(
                'Unauthorized to access this resource'
            );
        }
    }



    public function findByEmail(string $email): Candidate
    {
        $entity = $this->em->getRepository(CandidateEntity::class)->findOneBy([
            "email" => $email
        ]);

        if(!$entity){
            throw new RessourceNotFound("Candidate not found");
        }
        $candidate = $this->mapper->toDomain($entity);
        return $candidate;
    }



    public function findById(string $uuid): ?Candidate
    {
        $entity = $this->em
            ->getRepository(CandidateEntity::class)
            ->find($uuid);

        if ($entity === null) {
            return null;
        }

        return $this->mapper->toDomain($entity);
    }

    

    #[Override]
    public function getCandidateLightModel(string $candidateId): CandidateLightModel
    {
        $result = $this->em->createQueryBuilder()
            ->select(
                'c.id', 'c.firstName', 'c.lastName, c.email ',
                'f.id AS imageId, f.name AS imageName',
                'addr.id AS addressId, addr.city, addr.postalCode, addr.country, addr.street'
            )
            ->from(CandidateEntity::class, 'c')
            ->leftJoin('c.image', 'f')
            ->leftJoin('c.address', 'addr')
            ->where('c.id = :candidateId')
            ->setParameter('candidateId', $candidateId)
            ->getQuery()
            ->getOneOrNullResult(); // Assoc array

        if (!$result) {
            throw new RessourceNotFound("Candidate with ID {$candidateId} not found.");
        }

        // Image
        $imageUrl = $result['imageName'] 
            ? $result['imageName'] 
            : null;

        return new CandidateLightModel(
            id: $result['id'],
            firstName: $result['firstName'],
            lastName: $result['lastName'],
            email: $result['email'],
            imageId: $result["imageId"],
            address: Address::hydrate(
                id: $result['addressId'],
                city: $result['city'],
                country: $result['country'],
                street: $result['street'],
                postalCode: $result['postalCode']
            )->toArray()
        );
    }



    public function delete(string $uuid): void
    {
        throw new \Exception('Not implemented');
    }


    #[Override]
    public function apply(string $candidateId, string $offerId): void
    {
        throw new \Exception('Not implemented');
    }


    #[Override]
    public function getResumeFiles(string $candidateId): array
    {
        $candidate = $this->em->find(CandidateEntity::class, $candidateId);

        if(!$candidate){
            throw new RessourceNotFound('Candidate not found');
        }

        /** @var array<int, CandidateResumeEntity> $resumes */
        $resumes = $this->em->getRepository(CandidateResumeEntity::class)->findBy([
            'candidate' => $candidate
        ]);

        $result = [];
        foreach($resumes as $resume){
            $file = $resume->getFile();
            $result[$resume->getId()] = $this->fileMapper->toStaticDomainMedia($file);
        }

        return $result;
    }


    #[Override]
    public function getResumeFile(string $fileId): ?StaticMedia
    {
        $repository = $this->em->getRepository(CandidateResumeEntity::class);

        /** @var CandidateResumeEntity|null $found */
        $found = $repository->findOneBy([
            'file' => $fileId,
        ]);

        if(!$found)
            return null;

        return FileEntityMapper::toStaticDomainMedia($found->getFile()); 
    }


    #[Override]
    public function findResumeById(
        string $candidateId,
        string $fileId
    ): ?StaticMedia {
        $candidate = $this->em->find(
            CandidateEntity::class,
            $candidateId
        );

        if (!$candidate) {
            throw new RessourceNotFound('Candidate not found');
        }

        $repository = $this->em->getRepository(
            CandidateResumeEntity::class
        );

        /** @var CandidateResumeEntity|null $resume */
        $resume = $repository->findOneBy([
            'candidate' => $candidate,
            'file' => $fileId,
        ]);

        if (!$resume) {
            return null;
        }

        return $this->fileMapper->toStaticDomainMedia(
            $resume->getFile()
        );
    }


    #[Override]
    public function getResumeFileForCandidate(string $candidateId, string $fileId): ?StaticMedia
    {
        $repository = $this->em->getRepository(CandidateResumeEntity::class);

        /** @var CandidateResumeEntity|null $found */
        $found = $repository->findOneBy([
            'file' => $fileId,
            "candidate" => $candidateId
        ]);

        if(!$found)
            return null;

        return FileEntityMapper::toStaticDomainMedia($found->getFile()); 
    }


    #[Override]
    public function getCandidateSkills(string $candidateId): array
    {
        throw new \Exception('CandidateRepository::getCandidateSkills Not implemented');
    }


    // public function save(Candidate $candidate): string
    // {
    //     $entity = $this->em->getRepository(CandidateEntity::class)->find($candidate->id());

    //     if ($entity === null) {
    //         $entity = $this->mapper->toEntity($candidate);
    //         $this->em->persist($entity);
    //     }
    //     else {
    //         $this->mapper->updateEntity(
    //             entity: $entity,
    //             domain: $candidate,
    //         );
    //     }

    //     $this->em->flush();

    //     return $entity->getId();
    // }

    public function save(Candidate $candidate): string
    {
        $entity = $this->em
            ->getRepository(CandidateEntity::class)
            ->find($candidate->id());

        if ($entity === null) {
            $entity = $this->mapper->toEntity($candidate);
            $this->em->persist($entity);
        } else {
            $this->mapper->updateEntity(
                entity: $entity,
                domain: $candidate,
            );
        }

        ApiResponse::$logger->error("Exécution du repository");
        ApiResponse::$logger->error(json_encode([
            'entity_firstName' => $entity->getFirstName(),
            'entity_lastName' => $entity->getLastName(),
            'entity_email' => $entity->getEmail(),
            'entity_description' => $entity->getDescription(),
        ]));

        $this->em->flush();

        return $entity->getId();
    }
}