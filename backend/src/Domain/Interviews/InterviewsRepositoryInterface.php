<?php

namespace App\Domain\Interviews;

interface InterviewsRepositoryInterface
{
    /**
     * @param array $criteria
     *      ex: [
     *              'companyId'  => $companyId,
     *              'jobOfferId' => $jobId,
     *          ]
     */
    public function count(array $criteria);


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
     *      description?: bool,
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
     * @return array
     */
    public function fetchJobInterviewsProjection(
        string $jobId,
        int $limit = 17,
        int $skip = 0,
        array $scheme = ['id' => true],
        ?string $userId = null,
        ?string $companyId = null,
        ?string $candidateId = null
    ): array ;
}