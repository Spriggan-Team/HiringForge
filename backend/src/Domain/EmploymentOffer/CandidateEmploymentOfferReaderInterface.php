<?php


namespace  App\Domain\EmploymentOffer;


/**
 * Specialize interface in reading employment offer 
 * data.
 */
interface CandidateEmploymentOfferReaderInterface
{
    /**
    * Retrieve an employment offer for a candidate.
    * @param array{
    *    jobTitle?:string,
    *    menu: EmploymentOfferMenu 
    * } $criteria
    *
    * @return array{
    *   data: array<int,array{
    *     id: string,
    *     status: EmploymentOfferStatus,
    *     salary: float|null,
    *     message: string|null,
    *     application: array{id: string},
    *     jobOffer: array{
    *         id: string,
    *         title: string
    *     },
    *     company:array{
    *         id: string,
    *       name: string,
    *       logo?: array{
    *            id: int,
    *            name: string,
    *            mime:string,
    *        }
    *     },
    *     rejectionReason: string|null,
    *     scheduledEndDate: string,
    *     expiredAt: string,
    *     createdAt: string
    *   }>,
    *   total: int
    *}
    */
    public function fetchEmploymentOffersForCandidate(
        string $candidateId,
        array $criteria = [],
        int $skip = 0,
        int $limit = 15,
    ): array;



    /**
     * Retreive kpi/stats data about user's employment offers
     * @return array{
     *      awaiting: int,
     *      accepted: int,
     *      completed: int,
     *      rejected: int
     * }
     */
    public function fetchEmploymentOffersStatsForCandidate(string $candidateId): array;
}