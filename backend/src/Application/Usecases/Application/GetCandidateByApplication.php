<?php

namespace App\Application\Usecases\Application;

use App\Application\DTO\Candidate\CandidateApplication;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\User\UserRepositoryInterface;

class GetCandidateByApplication
{

    public function __construct(
        private ApplicationRepositoryInterface $applicationRepository,
        private UserRepositoryInterface $userRepository
    ){}


    /**
     * @param string $userId - recruiter id
     * @return array<int,CandidateApplication>
     */
    public function execute(string $userId, string $query): array
    {
        $this->userRepository->assertExist(uuid: $userId);
        $candidates = $this->applicationRepository->findCandidateApplicationsBySearchTerm(
            recruiterId: $userId,
            query: $query
        );
        return $candidates;
    }
}