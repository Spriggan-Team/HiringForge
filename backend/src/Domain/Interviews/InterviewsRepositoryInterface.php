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
}