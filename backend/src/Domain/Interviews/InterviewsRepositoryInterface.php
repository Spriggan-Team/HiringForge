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
     * @param string $userId - refers to recruiter's id
     * @return array<int, array{
     *      id: string,
     *      type: string,
     *      title: string,
     *      candidate: array{
     *          id: string,
     *          firstName: string,
     *          email: string,
     *          lastName: string,
     *          imageId?: int
     *      },
     *      description?: string,
     *      startDate: \DateTimeImmutable,
     *      rejectionReason?: string,
     *      minutes: int
     * }>
     */
    public function getInterviewAgenda(
        string $userId, 
        int $skip,
        int $limit,
        \DateTimeImmutable $date
    ) : array;


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


    /**
     * Retrieve the user's interviews for a given month, grouped by day.
     *
     * @return array<string, array<int, array{
     *     id: string,
     *     startDate: string,
     *     title: ?string,
     *     type: ?InterviewType,
     *     status: InterviewStatus
     * }>>
     *
     * The array key represents the interview day using the `Y-m-d` format.
     */
    public function getCalendarCollectionViews(
        string $userId,
        \DateTimeImmutable $month
    ): array;


    /**
     * Get interview details.
     *
     * @return array{
     *     id: string,
     *     startDate: string,
     *     title: ?string,
     *     type: ?InterviewType,
     *     status: InterviewStatus,
     *     description: string,
     *     candidateApproval: bool,
     *     rejectionReason: ?string,
     *     createdAt: string
     * }
     */
    public function getInterviewDetails(
        string $userId,
        string $interviewId
    ): array;


    /**
     * Get interview details scheduled for a specific day.
     *
     * @return array<int, array{
     *     id: string,
     *     startDate: string,
     *     title: ?string,
     *     type: ?InterviewType,
     *     status: InterviewStatus,
     *     description: ?string,
     *     candidateApproval: bool,
     *     rejectionReason: ?string,
     *     createdAt: string
     * }>
     */
    public function getInterviewDetailsByDay(
        string $userId,
        \DateTimeImmutable $day
    ): array;

    /**
     * Retrieve interviews related to a specific job linked to a recruiter (user),
     * a company, or a specific candidate.
     *
     * @param string $jobId
     * @param int $limit
     * @param int $skip
     * @param array{
     *      id?: bool,
     *      startDate?: bool,
     *      minutes?: bool,
     *      title?: bool,
     *      candidateApproval?: bool,
     *      description?: bool,
     *      url?: bool,
     *      status?: bool,
     *      candidate?: array{
     *          id?: bool,
     *          firstName?: bool,
     *          lastName?: bool,
     *          email?: bool,
     *          image?: bool|array{
     *              id?: bool,
     *              name?: bool,
     *              size?: bool,
     *              mime?: bool
     *          }
     *      }
     * } $scheme
     * @param string|null $userId
     * @param string|null $companyId
     * @param string|null $candidateId
     * @param array<int,InterviewStatus|null $statuses
     * @return array
     */
    public function fetchJobInterviewsProjection(
        string $userId,
        int $skip = 0,
        int $limit = 17,
        ?string $jobId = null,
        array $scheme = ['id' => true],
        ?string $companyId = null,
        ?string $candidateId = null,
        ?array $statuses= null,
    ): array ;
}