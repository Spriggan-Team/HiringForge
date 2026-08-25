<?php

namespace App\Domain\Candidate\Application\Repositories;

use App\Domain\Candidate\Application\Application;
use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\Exception\UnauthorizedAction;
use App\Domain\File\StaticMedia;

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
     * Verify if a resume id linke to an application
     */
    public function hasApplicationsUsingResume(string $candidateId, string $resumeId): bool; 

    /**
     * Verifies that the application belongs to the current candidate.
     *
     * @throws UnauthorizedAction if the application does not belong to the candidate.
     */
    public function assertApplicationBelongsToCandidate(string $candidateId, string $applicationId): void;


    /**
     * @param array<int,string> $applicationIds
     * @return array<string,JobApplicationStatus> array<id, status>
     */
    public function getStatusesByIds(array $applicationIds): array;


    /**
     * @return array{
     *  applicationId: string,
     *  candidateId: string,
     *  companyId: string,
     *  recruiterId: string
     * }
     */
    public function getApplicationIdentity(string $applicationId): ?array;

    /**
     * Get candidates idntity
     */
    public function getCandidateIdentity(string $applicationid):string;

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
    public function countApplications(array $criteria): int;



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
     *      },
     *      jobOffer?: array{
     *          id?: bool,
     *          title?: bool
     *      } 
     * } $scheme
     * @param string|null $userId
     * @param string|null $companyId
     * @param string|null $search allows used to search applications based on userName
     * @param string|null $status allows seacrh to be based on status
     * @return array
     */
    public function fetchJobApplicationsProjection(
        ?string $jobId, 
        int $limit = 17, int $skip = 0, 
        array $scheme = ['id'=>true], 
        ?string $userId =null, ?string $companyId = null,
        ?string $search = null,
        ?JobApplicationStatus $status =null,
    ): array;


    
    /**
     * Change job satus
     */
    public function changeStatus(string $applicationId, JobApplicationStatus $newStatus): void;


    /**
     * Save a job application to a job
     * @return string the $id of the application
     */
    public function save(Application $application): string;
    

    /**
     * Change job offer status
     * @param array<int,string> $applicationIds
     * @param JobApplicationStatus  $newStatus
     * @return void
     */
    public function bulkChangeStatus(array $applicationIds, JobApplicationStatus $newStatus): void;



    /**
     * Counts applications created within a specific date range, optionally filtered by job offer.
     */
    public function countApplicationsInPeriod(
        string $userId,
        \DateTimeInterface $start,
        \DateTimeInterface $end,
        ?string $jobOfferId = null
    ): int;



    /**
     * Calculates average time to hire in days for a specific job offer OR all user jobs.
     */
    public function getAvgTimeToHireDays(string $userId, ?string $jobOfferId =null): int;


    /**
     * Calculates baseline average time to hire across ALL job offers owned by the user
     * to compute the relative difference (+/- days vs user average).
     */
    public function getUserAvgTimeToHireDays(string $userId): int;

    /**
     * Retrieves the number of applications made by candidates for a specific job offer within a timeframe.
     *
     * @param string $userId The recruiter ID (verifies ownership/relation)
     * @param string|null $jobId  The unique job offer ID
     * @param string $timeframe 'month' (12 items) or 'week' (7 items)
     * @return array<int, float> List of postulation counts ordered chronologically
     */
    public function getPostulationMetrics(string $userId, ?string $jobId = null, string $timeframe = 'month'): array;


    /**
     * Generates 12 elements for the current year (Jan to Dec).
     * 
     * @return float[]
     */
    public function getMonthlyMetrics(string $userId, ?string $jobId, \DateTimeImmutable $now): array;



    /**
     * Generates 7 elements for the current week (Mon to Sun).
     * 
     * @return float[]
     */
    public function getWeeklyMetrics(string $userId, ?string $jobId, \DateTimeImmutable $now): array;


    /**
     * Verify if an user , a recuiter and an application
     * are all contected
     */
    public function assertRecruiterHasAccessToApplication(
        string $recruiterId,
        string $applicationId,
        ?string $candidateId = null
    ): void;


    /**
     * Check if an list of applications is linked to an user (recruiter)
     */
    public function assertRecruiterHasAccessToApplicationCollection(string $recruiterId, array $applicationIds): void;

    

    /**
     * Get status og a user
     */
    public function getApplicationStatus(string $applicationId): JobApplicationStatus;

    /**
     * Retreive information about the resume file that has been
     * used for a specific application
     */
    public function getResumeFile(
        string $applicationId,
        string $candidateId
    ): StaticMedia;
}