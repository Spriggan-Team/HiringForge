<?php

namespace App\Application\Command\Usecase\Auth;

use App\Application\DTO\AuthentificateActor;

use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Shared\PasswordHasherInterface;

class CandidateAuthentificator
{
    public function __construct(
        private CandidateRepositoryInterface $repository,
        private PasswordHasherInterface $hasher
    )
    {}

    public function execute(AuthentificateActor $actor): ?string
    {
        $candidate = $this->repository->findByEmail($actor->email);
        if($candidate && $this->hasher->verify($actor->password, $candidate->passwordHash())){
            return $candidate->id();
        }
        return null;
    }
}