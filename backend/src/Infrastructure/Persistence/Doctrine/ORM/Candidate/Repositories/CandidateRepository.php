<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate\Repositories;

use App\Domain\File\StaticMedia;
use App\Domain\Candidate\Candidate;
use App\Domain\Candidate\CandidateLightModel;
use App\Domain\Exception\RessourceNotFound;
use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Shared\Address;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateResumeEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\Mapper\FileEntityMapper;

use Doctrine\ORM\EntityManagerInterface;

use Override;


class CandidateRepository implements CandidateRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private FileEntityMapper $fileMapper
    ){}

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
    

    public function findByEmail(string $email): Candidate
    {
        $entity = $this->em->getRepository(CandidateEntity::class)->findOneBy([
            "email" => $email
        ]);

        if(!$entity){
            throw new RessourceNotFound("Candidate not found");
        }
        $candidate = CandidateEntityMapper::toDomain($entity);
        return $candidate;
    }


    public function findById(string $uuid): Candidate
    {
        throw new \Exception('Not implemented');
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


    public function save(Candidate $candidate): void
    {
        $entity = CandidateEntityMapper::toEntity($candidate);
        $this->em->persist($entity);
        $this->em->flush();
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
            $result[] = $this->fileMapper->toStaticDomainMedia($file);
        }

        return $result;
    }


    #[Override]
    public function findResumeById(
        string $candidateId,
        string $resumeId
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
            'file' => $resumeId,
        ]);

        if (!$resume) {
            return null;
        }

        return $this->fileMapper->toStaticDomainMedia(
            $resume->getFile()
        );
    }
}