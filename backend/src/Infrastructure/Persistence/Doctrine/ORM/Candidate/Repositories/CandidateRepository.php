<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate\Repositories;

use App\Domain\File\StaticMedia;
use App\Domain\Candidate\Candidate;
use App\Domain\Candidate\CandidateLightModel;
use App\Domain\Exception\RessourceNotFound;
use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;


use Doctrine\ORM\EntityManagerInterface;

use Override;


class CandidateRepository implements CandidateRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ){}


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
            ->select('c.id', 'c.firstName', 'c.lastName, c.email ' ,'f.name AS imageName')
            ->from(CandidateEntity::class, 'c')
            ->leftJoin('c.image', 'f')
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
            imageUrl: $imageUrl
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
    public function getCVFile(string $candidate): ?StaticMedia
    {
        throw new \Exception('Not implemented');
    }
}