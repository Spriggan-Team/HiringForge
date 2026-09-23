<?php


namespace App\Application\Query\Interviews\Repositories;

use App\Domain\Interviews\InterviewType;
use App\Domain\Interviews\InterviewStatus;


interface CandidateInterviewsQueryRepositoryInterface
{
    /**
     * @param string $candidateId - refers to candidate's id
     * @param int $skip - amount of result to ignore in a row
     * @param int $limit - max amount of result to retreive
     * @param \DateTimeImmutable $date - specific day within the quuery shoulb be exetuted
     * @param array<int, InterviewStatus> $statuses - filter by status
     * @return array<int, array{
     *      id: string,
     *      type: string,
     *      title: string,
     *      company: array{
     *          id: string,
     *          name: string,
     *          logo?: array{
     *              name: string,
     *              mime: string
     *          },
     *      },
     *      status: InterviewStatus,
     *      description?: string,
     *      startDate: \DateTimeImmutable,
     *      rejectionReason?: string,
     *      minutes: int
     * }>
     */
    public function getInterviewAgenda(
        string $candidateId, 
        \DateTimeImmutable $date,
        int $skip = 0,
        int $limit = 15,
        array $statuses = [],
    ) : array;


    /**
     * Retrieve the candidate's interviews for a given month, grouped by day.
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
        string $candidateId,
        \DateTimeImmutable $month,
    ): array; 
}