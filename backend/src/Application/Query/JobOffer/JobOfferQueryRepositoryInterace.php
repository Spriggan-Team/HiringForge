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



    /**
     * This function is a very powerful/flexible one that helps a user retrieve 
     * dynamically targeted data fields about a specific job offer.
     *
     * @param string $jobOfferId The target job offer ID
     * @param string $userId The ID of the related user (recruiter) 
     * @param array  $scheme An array that describes the output data shape
     *   ex: [
     *       'id'? => bool,
     *       'title'? => bool,
     *       'content'? => bool,
     *       'jobWorkMode'? => bool,
     *       'viewsCount'? => bool,       // Returns the total number of views
     *       'mainImage'? => bool,        // Returns the image name
     *       'createdAt'? => bool,
     *       'updatedAt'? => bool,
     *       'salary'? => [
     *           'devise'? => bool,
     *           'min'? => bool,
     *           'max'? => bool,
     *       ],
     *       'location'? => [
     *           'id'? => bool,
     *           'street'? => bool,
     *           'city'? => bool,
     *           'country'? => bool,
     *       ],
     *       'department'? => [
     *           'id'? => bool,
     *           'label'? => bool,
     *       ],
     *       'contract'? => [
     *           'id'? => bool,
     *           'label'? => bool,
     *       ],
     *       'skills'? => [               // Produces an array of skills
     *           'id'? => bool,
     *           'name'? => bool,
     *           'local'? => string,
     *       ],
     *       'languages'? => [            // Produces an array of languages
     *           'label'? => bool,
     *           'level'? => bool,
     *       ],
     *      'publicationStatus'? => bool,
     *      'activityStatus'? => bool,
     *      'visibilityStatus' =>bool
     *   ]
     * @return array
     */
    public function fetchJobOfferProjection(string $jobOfferId, string $userId, array $scheme = []): array;
}