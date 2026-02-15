<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate;

use App\Domain\Candidate\Candidate;
use App\Domain\Exception\RessourceNotFound;
use App\Domain\Candidate\CandidateRepositoryInterface;

use App\Domain\Shared\EmailAddress;
use App\Domain\Sharedp\KnownIdentity;

use Doctrine\ORM\EntityManagerInterface;


class CandidateRepository implements CandidateRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ){}

    public function exists(?string $uuid = null, ?EmailAddress $email = null): KnownIdentity
    {
        throw new \Exception('Not implemented');
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

    public function findAll(?int $skip=null, ?int $limit = null): array
    {
        throw new \Exception('Not implemented');
    }


    public function save(Candidate $candidate): void
    {
        $entity = CandidateEntityMapper::toEntity($candidate);
        $this->em->persist($entity);
        $this->em->flush();
    }


    public function changeEmail(string $email): void
    {
        throw new \Exception('Not implemented');
    }


    public function changePassword(string $email, string $hash): void
    {
        throw new \Exception('Not implemented');
    }


    public function delete(string $uuid): void
    {
        throw new \Exception('Not implemented');
    }
}