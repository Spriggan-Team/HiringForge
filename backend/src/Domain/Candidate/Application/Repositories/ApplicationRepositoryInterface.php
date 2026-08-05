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
     * count all related application of an user to a job
     * @param array $criteria
     *          ex: [
     *              'companyId' => string,
     *              'jobOfferId' => string,
     *              'status'? => JobApplicationStatus
     *          ]
    */
    public function count(array $criteria): int;

    /**
     * Retrieve all applications/postulation related to a specific job
     * and a user (recruiter) or a company if passed.
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
     * retreive the number of postaltion made by candidate in a timeframe
     * @param string $userId the idntifiant of an user! it is used to check the relation between the user (recruiter) & the related job
     * @param string $jobId the uniq identifier of a kob
     * @param string $timeframe determine wether the results are sort by 'month' (12 items) or by 'week' (7 items)
     * @return array<int, float>
     */
    public function getPostulationMetrics(string $userId ,string $jobId, string $timeframe = 'month'): array;

}