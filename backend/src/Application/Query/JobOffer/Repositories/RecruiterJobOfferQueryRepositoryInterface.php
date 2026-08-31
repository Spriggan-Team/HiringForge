<?php


namespace App\Application\Query\JobOffer\Repositories;

use App\Application\Query\JobOffer\DTO\JobOfferListItem;
use App\Application\Query\JobOffer\DTO\JobOfferViewLightModel;
use App\Application\Query\JobOffer\DTO\JobSummaryItem;
use App\Domain\JobOffer\JobActivityStatus;
use App\Domain\JobOffer\JobPublicationStatus;

interface RecruiterJobOfferQueryRepositoryInterface
{
    /**
     * Fetch statistical cardinalities for a specific job offer application pipeline.
     *
     * @param string $jobId The unique identifier of the job offer.
     * 
     * @return array{
     *     candidatesCount: int,
     *     interviewsCount: int,
     *     employmentOfferCount: int,
     *     hiredCount: int,
     *     viewsCount: int
     * } Array containing the count metrics for each application stage.
     */
    public function fetchJobOfferCardinalities(string  $recruiterId, string $jobId): array;


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
     * @param array{
     *          publishedState: JobPublicationStatus,
     *          salary: int,
     *          candidateCount: int,
     *          searchText: string,
     *          searchAddress: string
     * } $criteria this is used for filter the reponse thta should be returned 
     *      - salary  (between minSalry or maxSlary),
     * @return array<int,JobSummaryItem>
     *     Collection of serializable job offer view models.
     */
    public function fetchJobOfferViewCollection(
        ?string $userId = null,
        ?int $limit = null,
        ?int $skip = null,
        array $criteria = []
    ): array;



    /**
     *  Retrieve job offers matching criteria.
     *  @return array<int,JobOfferViewLightModel> 
     */
    public function getJobOfferLightViewModelByCriteria(
        string $userId,
        JobActivityStatus $activityStatus,
        int $skip = 0,
        int $limit = 5
    ): array;



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
     * Retrieves a dynamically defined projection of a job offer.
     *
     * The requested fields are defined by the provided schema, allowing callers
     * to retrieve only the data they need, including nested related entities
     * such as location, department, contract, skills, and languages.
     *
     * @param string $jobOfferId The ID of the job offer to retrieve.
     * @param string $userId The ID of the related recruiter/user.
     * @param array<string, bool|array<string, mixed>> $scheme
     *        Defines the shape of the returned data.
     *
     *        Example:
     *        [
     *            'id' => true,
     *            'title' => true,
     *            'content' => true,
     *            'jobWorkMode' => true,
     *            'viewsCount' => true,          // Total number of views.
     *            'mainImage' => true,           // Image name.
     *            'mainImageId' => true,
     *            'createdAt' => true,
     *            'updatedAt' => true,
     *            'salary' => [
     *                'currency' => true,
     *                'min' => true,
     *                'max' => true,
     *            ],
     *            'location' => [
     *                'id' => true,
     *                'street' => true,
     *                'city' => true,
     *                'country' => true,
     *            ],
     *            'department' => [
     *                'id' => true,
     *                'label' => true,
     *            ],
     *            'contract' => [
     *                'id' => true,
     *                'label' => true,
     *            ],
     *            'skills' => [
     *                'id' => true,
     *                'name' => true,
     *                'local' => true,
     *            ],
     *            'languages' => [
     *                'label' => true,
     *                'level' => true,
     *            ],
     *            'publicationStatus' => true,
     *            'activityStatus' => true,
     *            'visibilityStatus' => true,
     *        ]
     *
     * @return array<string, mixed> The job offer data matching the requested schema.
     */
    public function fetchJobOfferProjection(string $jobOfferId, string $userId, array $scheme = []): array;


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
     * Returns all skill IDs associated with a specific job.
     *
     * @return array<int, string> List of skill IDs (UUIDs or string identifiers).
    */
    public function getSkillIdsByJobId(string $jobId): array;
}