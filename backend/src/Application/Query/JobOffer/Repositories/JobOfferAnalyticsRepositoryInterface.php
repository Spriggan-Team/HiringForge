<?php

namespace App\Application\Query\JobOffer\Repositories;

use App\Application\Query\JobOffer\DTO\JobOfferStatistics;

interface JobOfferAnalyticsRepositoryInterface
{
    /** 
     * Retreive job' views grouped by month
     * 
    */
    public function fetchViewsGroupedByMonth(
        string $userId,
        string $jobId,    
    );


    
    /**
     * Retrieves aggregated candidate/application statistics for a given user or job offer.
     *
     * @param string $userId
     * @param string|null  $jobId If null, stats are calculated for all jobs belonging to the user
     * @return array{
     *      preselect: int,
     *      interviews: int,
     *      rejected: int,
     *      offer: int
     * }
     */
    public function getJobStats(string $userId, ?string $jobId = null): array;    

    
    /**
     * Analyzes and counts the job offers associated with a user.
     * Returns statistics such as active, open, and pending-review offers.
     */
    public function analyseJobOfferCollection(string $userId):  JobOfferStatistics;
}