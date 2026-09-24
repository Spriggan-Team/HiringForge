<?php

namespace App\Application\Query\Interviews\Repositories;

use App\Domain\Interviews\InterviewStatus;
use App\Domain\Interviews\InterviewType;

interface RecruiterInterviewsQueryRepositoryInterface
{

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
     *      job?: array{
     *          title?: bool   
     *      },
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


    /**
     * @param string $userId - refers to recruiter's id
     * @return array<int, array{
     *      id: string,
     *      type: string,
     *      title: string,
     *      url?: string,
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
}