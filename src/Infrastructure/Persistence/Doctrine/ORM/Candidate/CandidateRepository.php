<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate;

use App\Domain\Candidate\Candidate;
use App\Domain\Shared\Actor\Actor;
use App\Domain\Candidate\CandidateRepositoryInterface;

use Doctrine\ORM\EntityManagerInterface;


class CandidateRepository implements CandidateRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ){}

    public function findByEmail(string $email): ?Candidate
    {
        $entity = $this->em->getRepository(CandidateEntity::class)->findOneBy([
            "email" => $email
        ]);

        if(!$entity){
            return null;
        }
        $candidate = CandidateEntityMapper::toDomain($entity);
        return $candidate;
    }


    public function findById(string $uuid): ?Candidate
    {
        throw new \Exception('Not implemented');
    }

    public function findAll(): array
    {
        throw new \Exception('Not implemented');
    }

    public function save(Candidate $candidate): void
    {
        throw new \Exception('Not implemented');
    }
}