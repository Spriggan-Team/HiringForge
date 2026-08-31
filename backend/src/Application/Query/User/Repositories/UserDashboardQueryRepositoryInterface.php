<?php

namespace App\Application\Query\User\Repositories;

interface UserDashboardQueryRepositoryInterface
{
    /**
     * @return array{
     *      applications: array<int,int>,
     *      interviews: array<int,int>,
     *      hires: array<int,int>,
     *      applicationCount: int,
     *      interviewsCount: int,
     *      hiredCandidateCount: int
     * }
     */
    public function getDashboardStats(
        string $recruiterId, 
        \DateTimeImmutable $date, 
        string $timeframe = "week"
    ): array;

}