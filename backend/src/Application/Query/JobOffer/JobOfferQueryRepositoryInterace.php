<?php

namespace App\Application\Query\JobOffer;

use App\Application\Query\JobOffer\DTO\JobOfferStatistics;
use App\Application\Query\JobOffer\DTO\JobOfferListItem;
use App\Domain\JobOffer\JobPublicationStatus;

interface JobOfferQueryRepositoryInterace
{
    /**
     * This return a view of a offer in the bdd.
     *  @throws RessourceNotFound
     *  @return JobOfferListItem
     */
    public function fetchJobOfferViewById(
        string $userId,
        string $offerId
    ): JobOfferListItem;


    /** 
     * Retreive job' views grouped by month
     * 
    */
    public function fetchViewsGroupedByMonth(
        string $userId,
        string $jobId,    
    );


    /**
     * Retrieves a collection of job offers.
     *
     * By default, only published job offers are returned. To retrieve job offers
     * with any other status, the identifier of the requesting user must be provided.
     *
     * Results are ordered by creation date in descending order and support pagination.
     *
     * @param string|null $userId
     *     Identifier of the user performing the retrieval.
     *     Optional when fetching published job offers, but required when
     *     requesting offers with a status other than JobPublicationStatus::PUBLISHED.
     *
     * @param int|null $limit
     *     Maximum number of job offers to return.
     *
     * @param int|null $skip
     *     Number of job offers to skip from the beginning of the result set.
     *
     * @param JobPublicationStatus|null $category
     *     Status used to filter job offers.
     *     Defaults to JobPublicationStatus::PUBLISHED. When a different status is specified,
     *     the $userId parameter must be provided.
     *
     * @return JobOfferListItem[]
     *     Collection of serializable job offer view models.
 */
    public function fetchJobOfferViewCollection(
        ?string $userId = null,
        ?int $limit = null,
        ?int $skip = null,
        ?JobPublicationStatus $category = JobPublicationStatus::PUBLISHED    
    ): array;


    /**
     * Analyzes and counts the job offers associated with a user.
     * Returns statistics such as active, open, and pending-review offers.
     */
    public function analyseJobOfferCollection(string $userId): JobOfferStatistics;
}