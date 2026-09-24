<?php

namespace App\Domain\Interviews;


interface InterviewsRepositoryInterface
{
    /**
     * Retrieves the contextual information associated with an interview.
     *
     * This method returns a lightweight projection containing the information
     * required by application use cases without loading the complete
     * Interview aggregate.
     *
     * @param string $interviewId The interview identifier.
     *
     * @return InterviewContext|null The interview context, or null if no interview
     *                               matches the given identifier.
     */
    public function getInterviewContext(
        string $interviewId
    ): ?InterviewContext;

    /**
     * Verify whether a recruiter is associated with a specific interview.
     */
    public function isUserAssociatedWithInterview(
        string $userId,
        string $interviewId
    ): bool;


    /**
     * Seek overlaping interview datetime
     * @throws \App\Domain\Exception\ConcurrentInterviewsException
     */
    public function findConcurrentInterviews(
        \DateTimeImmutable $startDate,
        int $minutes
    ): bool;


    /**
     * Check whether an interview has been confirmed by the candidate.
     */
    public function isConfirmedByCandidate(string $interviewId): bool;



    // -- Save Interviews
    public function save(Interview $interview): void;
    
    /** Remove an interview */
    public function remove(string $interviewId): void;


    /**
     * Assert relation between user - candidate - interview
     * @throws \Exception
     */
    public function assertRecruiterHasAccessToInterview(
        string $recruiterId,
        string $candidateId,
        string $interviewId
    ): void ;

    /**
     * Find by id
     */
    public function findById(string $id): ?Interview;



    /**
     * @param array $criteria
     *      ex: [
     *              'companyId'  => $companyId,
     *              'jobOfferId' => $jobId,
     *          ]
     */
    public function countInterviews(array $criteria);

    
    /**
     * Invalidate interview plans for an application
     * Turn all application of an user into CANCELELD State
     */
    public function cancelInterviewPlansForApplication(string $recruiterId, string $applicationId): void;



}