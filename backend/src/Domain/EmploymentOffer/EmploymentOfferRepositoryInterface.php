<?php

namespace  App\Domain\EmploymentOffer;

use  App\Domain\Candidate\Application\JobApplicationStatus;

interface EmploymentOfferRepositoryInterface
{
    /**
     * Check if a candidate has rightfully access to an employment offer
     */
    public function assertCandidateAccess(string $candidateId, string $employmentOfferId): void;

    /**
     * Check if application has some active employment offer  
     */
    public function hasActiveOffer(string $applicationId): bool;


    /**
     * Returns an array of offers related to a recruiter.
     *
     * @param ?string $userId The unique identifier of the user. If provided, the
     *                        retrieval is restricted to offers associated with this user.
     * @param ?string $companyId The unique identifier of the company. If provided,
     *                           the retrieval considers all offers within the company
     *                           instead of being restricted to a single user.
     * @param ?string $jobId The unique identifier of a job. If provided, only the
     *                       offers related to this job are returned. If null,
     *                       offers from all jobs are considered.
    * @param array{
    *      'id'?: bool,
    *      'message'?: bool,
    *      'salary'?: bool,
    *      'status'?: bool,
    *      'expiredAt'?: bool,
    *      'createdAt'?: bool,
    *      'sentAt'?: bool,
    *      'message'?: bool,
    *      'scheduledEndDate'?: bool,
    *      'rejectionReason'?: bool,
    *      'candidate'?: array{
    *          'id'?: bool,
    *          'firstName'?: bool,
    *          'lastName'?: bool,
    *          'email'?: bool,
    *          'image'?: array{
    *              'id'?: bool,
    *              'name'?: bool,
    *              'size'?: bool,
    *              'mime'?: bool
    *          }
    *      },
    *      'application'?: array{
    *          'id'?: bool
    *      },
    *      'jobOffer'?: array{
    *          'id'?: bool,
    *          'title'?: bool
    *      }
    *   }
    *   $scheme Defines which fields should be returned and shapes both the query and the returned data.
    */
    public function fetchOfferProjection(?string $userId= null, ?string $companyId =null, ?string $jobId = null, array $scheme = ['id' => true], int $limit= 17, int $skip=0 ): array;



    /**
     * Creates an offer after verifying that the user is associated with the related job.
     * It can be used to handle update
     * @param string $userId The unique identifier of the user.
     * @param EmploymentOffer $offer Domain object containing the data required to create an offer.
     */
    public function save(string $userId,  EmploymentOffer $offer): void;



    /**
     * @param array{
     *   userId?: string,
     *   status?: OfferStatus,
     *   jobId?: JobApplicationStatus,
     *   candidateId?: string,
     *   jobOfferId?: string
     * } $criteria
     * @return int
     */
    public function countOffers(array $criteria): int;



    /**
     * check if an it is possible to generate employment offer 
     * for an specirfic emplyement
     */
    public function canCreateEmploymentOfferForApplication(
        string $applicationId,
        string $candidateId
    ): bool;


    /**
     * find employment offer by id
     */
    public function findById(string $employmentId): ?EmploymentOffer;

    /**
     * Check relation and return employment offer
     */
    public function findEmploymentOfferForRecruiter(string $recruiterId, string $employmentOfferId): ?EmploymentOffer ;


    /**
     * Delete offer
     */
    public function delete(string $employmentId): void;


    /**
     * Assert active offer
     */
    public function assertNoActiveAcceptedOffer(
        string $candidateId,
        string $exceptEmploymentOfferId
    ): void;


    /**
     * Allow user to accept an employment offer
     */
    public function accept(string $candidateId,  EmploymentOffer $employment): void;



    public function refuse(string $candidateId,  EmploymentOffer $employment): void;
}