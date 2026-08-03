<?php

namespace App\Application\Query\JobOffer;

use App\Application\Query\JobOffer\DTO\JobOfferStatistics;
use App\Application\Query\JobOffer\DTO\JobOfferListItem;
use App\Application\Query\JobOffer\DTO\JobSummaryItem;
use App\Domain\JobOffer\JobPublicationStatus;

interface JobOfferQueryRepositoryInterace
{
    /**
     * This return a view of a offer in the bdd.
     * if userId specified, it only returns those associated to this user
     *  @throws RessourceNotFound
     *  @return JobOfferListItem
     */
    public function fetchJobOfferViewById(
        string $offerId,
        ?string $userId = null,
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
     *
     * @param array $criteria this is used for filter the reponse thta should be returned 
     *         ex: [
     *              'publishedState' => JobPublicationStatus,
     *              'salary' => number, (between minSalry or maxSlary),
     *              'candidateCount' => number,
     *              'searchText' => string,
     *              'searchAddress' => string
     *          ]
     * 
     * @return JobSummaryItem[]
     *     Collection of serializable job offer view models.
     */
    public function fetchJobOfferViewCollection(
        ?string $userId = null,
        ?int $limit = null,
        ?int $skip = null,
        array $criteria = []
    ): array;


    /**
     * Analyzes and counts the job offers associated with a user.
     * Returns statistics such as active, open, and pending-review offers.
     */
    public function analyseJobOfferCollection(string $userId):  JobOfferStatistics;


    /** 
     * count all job offer related to the specified user.
     * @param $userId the id (uniq identifier) of an user
     * @param $criteria the filters for count retreival
     *          ex: [
     *              'publishedState' => JobPublicationStatus,
     *              'salary' => number, (between minSalry or maxSlary),
     *              'candidateCount' => number,
     *              'searchText' => string,
     *              'searchAddress' => string
     *          ]
     * @return int
     */
    public function count(string $userId, array $criteria = []): int;
}