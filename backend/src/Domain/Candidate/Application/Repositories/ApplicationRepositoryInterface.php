<?php

namespace App\Domain\Candidate\Application\Repositories;

use App\Domain\Candidate\Application\JobApplicationStatus;

interface ApplicationRepositoryInterface
{
    /**
     * Checks whether a job application exists in the system.
     *
     * @param string $id The job application identifier
     * @throws Exception|DomainException is thrown when nothing is found
     */
    public function assertExists(string $id): void;


    /** 
     * Counts applications matching criteria. If no status is provided, all applications are counted.
     *
     * @param array{
     *     companyId?: string,
     *     jobOfferId?: string,
     *     userId?: string,
     *     status?: mixed
     * } $criteria
     */
    public function count(array $criteria): int;


    /**
     * Retrieve all applications/postulation related to a specific job,
     * to all job of an user (recruiter) or all job of company if passed.
     *
     * @param string $jobId
     * @param int $limit
     * @param int $skip
     * @param array{
     *      id?: bool,
     *      matchScore?: bool,
     *      status?: bool,
     *      appliedAt?: bool,
     *      candidate?: array{
     *          id?: bool,
     *          lastName?: bool,
     *          firstName?: bool,
     *          email?: bool,
     *          image?: bool|array{
     *              name?: bool,
     *              mime?: bool,
     *              size?: bool,
     *              createdAt?: bool
     *          }
     *      }
     * } $scheme
     * @param string|null $userId
     * @param string|null $companyId
     * @return array
     */
    public function fetchJobApplicationsProjection(string $jobId, int $limit = 17, int $skip = 0, array $scheme = ['id'=>true], ?string $userId =null, ?string $companyId = null  ): array;


    
    /**
     * Change job satus
     */
    public function changeStatus(string $applicationId, JobApplicationStatus $newStatus): void;

    
    /**
     * Retrieves aggregated candidate/application statistics for a given job offer.
     *
     * @param string $jobId
     * @return array{
     *      preselect: int,
     *      interviews: int,
     *      rejected: int,
     *      offer: int
     * }
     */
    public function getUserStats(string $userId, string $jobId): array;

    
    /**
     * Counts applications created within a specific date range.
     */
    public function countApplicationsInPeriod(string $jobOfferId, \DateTimeInterface $start, \DateTimeInterface $end): int;



    /**
     * Calculates average time to hire in days for a specific job offer and user.
     */
    public function getAvgTimeToHireDays(string $jobOfferId, string $userId): int;


    /**
     * Calculates baseline average time to hire across ALL job offers owned by the user
     * to compute the relative difference (+/- days vs user average).
     */
    public function getUserAvgTimeToHireDays(string $userId): int;

    /**
     * Retrieves the number of applications made by candidates for a specific job offer within a timeframe.
     *
     * @param string $userId The recruiter ID (verifies ownership/relation)
     * @param string $jobId  The unique job offer ID
     * @param string $timeframe 'month' (12 items) or 'week' (7 items)
     * @return float[] List of postulation counts ordered chronologically
     */
    public function getPostulationMetrics(string $userId, string $jobId, string $timeframe = 'month'): array;


    /**
     * Generates 12 elements for the current year (Jan to Dec).
     * 
     * @return float[]
     */
    public function getMonthlyMetrics(string $userId, string $jobId, \DateTimeImmutable $now): array;



    /**
     * Generates 7 elements for the current week (Mon to Sun).
     * 
     * @return float[]
     */
    public function getWeeklyMetrics(string $userId, string $jobId, \DateTimeImmutable $now): array;
}