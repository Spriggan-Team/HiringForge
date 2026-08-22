<?php

namespace  App\Domain\Offer;

use  App\Domain\Candidate\Application\JobApplicationStatus;

interface OfferRepositoryInterface
{
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
    *      'title'?: bool,
    *      'message'?: bool,
    *      'salary'?: bool,
    *      'status'?: bool,
    *      'expiredAt'?: bool,
    *      'sentAt'?: bool,
    *      'candidate'?: array{
    *          'id'?: bool,
    *          'firstName'?: bool,
    *          'lastName'?: bool,
    *          'email'?: bool,
    *          'image'?: array{
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
    * }
    *   $scheme Defines which fields should be returned and shapes both the query and the returned data.
    */
    public function fetchOfferProjection(?string $userId= null, ?string $companyId =null, ?string $jobId = null, array $scheme = ['id' => true], int $limit= 17, int $skip=0 ): array;

    /**
     * Creates an offer after verifying that the user is associated with the related job.
     *
     * @param string $userId The unique identifier of the user.
     * @param Offer $offer Domain object containing the data required to create an offer.
     */
    public function save(string $userId,  Offer $offer): void;


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
}