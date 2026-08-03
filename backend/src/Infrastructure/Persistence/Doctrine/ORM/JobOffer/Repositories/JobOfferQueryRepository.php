<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Repositories;

use App\Api\Responder\ApiResponse;
use App\Application\Query\JobOffer\DTO\JobOfferStatistics;
use App\Application\Query\JobOffer\DTO\JobOfferListItem;
use App\Application\Query\JobOffer\DTO\JobSummaryItem;
use App\Application\Query\JobOffer\JobOfferQueryRepositoryInterace;
use App\Domain\Candidate\Application\JobApplicationStatus;

use App\Domain\JobOffer\JobActivityStatus;
use App\Domain\JobOffer\JobPublicationStatus;
use App\Domain\Company\CompanyRepositoryInterface;
use App\Domain\Interviews\InterviewsRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Helpers\AddressSearchHelper;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;


use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use App\Domain\JobOfferApplication\Repositories\JobOfferApplicationRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Override;




/**
 * Doctrine-based read repository dedicated to job offer queries.
 *
 * This repository is responsible only for data retrieval optimized
 * for presentation or read use cases (queries).
 * 
 * It does NOT reconstruct domain aggregates and must not contain
 * business logic. Its purpose is to return lightweight projections
 * tailored for application query handlers.
 */

class JobOfferQueryRepository implements JobOfferQueryRepositoryInterace
{
    public function __construct(
        private EntityManagerInterface $manager,
        private JobOfferApplicationRepositoryInterface $applicationRepository,
        private InterviewsRepositoryInterface $interviewsRepository,
        private CompanyRepositoryInterface $companyRepositoryInterface
    ){}


    public function fetchJobOfferViewById(
        string $offerId,
        ?string $userId = null,
    ): JobOfferListItem
    {
        
        throw new \Exception('Not implemented');
    } 


    public function fetchJobOfferViewCollection(
        ?string $userId = null,
        ?int $limit = null,
        ?int $skip = null,
        array $criteria = []
    ): array {
        // Company Retreival
        $companyId = null;
        try {
            $company = $this->companyRepositoryInterface->fetchUserCompanyProjection(
                userId: $userId,
                scheme: ['id' => true]
            );
            $companyId = $company['id'] ?? null ? (string) $company['id'] : null;
        } catch (\Throwable $e) {
            $companyId = null;
        }

        // Main QueryBuilder 
        $qb = $this->manager->createQueryBuilder()
            ->select('
                j.id AS id, 
                j.title AS title, 
                j.activityStatus AS activityStatus, 
                j.publicationStatus AS publicationStatus, 
                ja.country AS country, 
                ja.street AS street, 
                ja.city AS city
            ')
            ->from(JobOfferEntity::class, 'j')
            ->leftJoin('j.user', 'u')
            ->leftJoin('j.address', 'ja');

        // User: Filter
        if (!empty($userId)) {
            $qb->andWhere('u.id = :userId')
            ->setParameter('userId', (string) $userId);
        }

        //  Extraction of the articles of incorporation selected for publication
        $pubStatuses = [];
        if (!empty($criteria['publishedState']) && is_array($criteria['publishedState'])) {
            if (filter_var($criteria['publishedState']['draft'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $pubStatuses[] = JobPublicationStatus::DRAFT->value;
            }
            if (filter_var($criteria['publishedState']['published'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $pubStatuses[] = JobPublicationStatus::PUBLISHED->value;
            }
            if (filter_var($criteria['publishedState']['closed'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $pubStatuses[] = JobPublicationStatus::CLOSED->value;
            }
        }

        //  Extract the selected statuses for the activity
        $actStatuses = [];
        if (!empty($criteria['offerState']) && is_array($criteria['offerState'])) {
            if (filter_var($criteria['offerState']['active'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $actStatuses[] = JobActivityStatus::ACTIVE->value;
            }
            if (filter_var($criteria['offerState']['pending'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $actStatuses[] = JobActivityStatus::PENDING->value;
            }
            if (filter_var($criteria['offerState']['inactive'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $actStatuses[] = JobActivityStatus::INACTIVE->value; // 'deactivate'
            }
        }

        // Applying OR Condition Filters (Filter Flexibility)
        if (!empty($pubStatuses) || !empty($actStatuses)) {
            $stateOrConditions = $qb->expr()->orX();

            if (!empty($pubStatuses)) {
                $stateOrConditions->add($qb->expr()->in('j.publicationStatus', ':pubStatuses'));
                $qb->setParameter('pubStatuses', $pubStatuses);
            }

            if (!empty($actStatuses)) {
                $stateOrConditions->add($qb->expr()->in('j.activityStatus', ':actStatuses'));
                $qb->setParameter('actStatuses', $actStatuses);
            }

            $qb->andWhere($stateOrConditions);
        }

        // Text Search Filter (Title)
        if (!empty($criteria['searchText'])) {
            $qb->andWhere('LOWER(j.title) LIKE :searchText')
            ->setParameter('searchText', '%' . mb_strtolower(trim($criteria['searchText'])) . '%');
        }

        //  Pagination
        if ($limit !== null && $limit > 0) $qb->setMaxResults($limit);
        if ($skip !== null && $skip > 0) $qb->setFirstResult($skip);

        $results = $qb->getQuery()->getArrayResult();

        if (empty($results)) {
            return [];
        }

        //  Formatting & DTO
        return array_map(function (array $value) use ($companyId): JobSummaryItem {
            $jobId = (string) $value['id'];

            $candidatesCount = $companyId ? (int) $this->applicationRepository->count(['companyId' => $companyId, 'jobOfferId' => $jobId]) : 0;
            $interviewsCount = $companyId ? (int) $this->interviewsRepository->count(['companyId' => $companyId, 'jobOfferId' => $jobId]) : 0;
            $hiredCount      = $companyId ? (int) $this->applicationRepository->count(['companyId' => $companyId, 'jobOfferId' => $jobId, 'status' => JobApplicationStatus::HIRED->value]) : 0;

            $addressParts = array_filter([$value['street'] ?? null, $value['city'] ?? null, $value['country'] ?? null]);

            $pubStatus = $value['publicationStatus'] instanceof \BackedEnum 
                ? $value['publicationStatus']->value 
                : (string) $value['publicationStatus'];

            $actStatus = $value['activityStatus'] instanceof \BackedEnum 
                ? $value['activityStatus']->value 
                : (string) $value['activityStatus'];

            return new JobSummaryItem(
                id: $jobId,
                title: (string) $value['title'],
                address: implode(', ', $addressParts),
                publicationStatus: $pubStatus,
                activityStatus: $actStatus,
                cardinal: [
                    'candidates' => $candidatesCount,
                    'interviews' => $interviewsCount,
                    'offers'     => 0,
                    'hired'      => $hiredCount,
                ]
            );
        }, $results);
    }



    public function count(
        ?string $userId = null,
        array $criteria = []
    ): int {
        // QueryBuilder 
        $qb = $this->manager->createQueryBuilder()
            ->select('COUNT(DISTINCT j.id)')
            ->from(JobOfferEntity::class, 'j')
            ->leftJoin('j.user', 'u');

        //Filter: User
        if (!empty($userId)) {
            $qb->andWhere('u.id = :userId')
            ->setParameter('userId', (string) $userId);
        }

        //  Extracting the articles selected for publication
        $pubStatuses = [];
        if (!empty($criteria['publishedState']) && is_array($criteria['publishedState'])) {
            if (filter_var($criteria['publishedState']['draft'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $pubStatuses[] = JobPublicationStatus::DRAFT->value;
            }
            if (filter_var($criteria['publishedState']['published'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $pubStatuses[] = JobPublicationStatus::PUBLISHED->value;
            }
            if (filter_var($criteria['publishedState']['closed'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $pubStatuses[] = JobPublicationStatus::CLOSED->value;
            }
        }

        // Extract the selected statuses for the activity
        $actStatuses = [];
        if (!empty($criteria['offerState']) && is_array($criteria['offerState'])) {
            if (filter_var($criteria['offerState']['active'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $actStatuses[] = JobActivityStatus::ACTIVE->value;
            }
            if (filter_var($criteria['offerState']['pending'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $actStatuses[] = JobActivityStatus::PENDING->value;
            }
            if (filter_var($criteria['offerState']['inactive'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $actStatuses[] = JobActivityStatus::INACTIVE->value; // 'deactivate'
            }
        }

        // Filter: State
        if (!empty($pubStatuses) || !empty($actStatuses)) {
            $stateOrConditions = $qb->expr()->orX();

            if (!empty($pubStatuses)) {
                $stateOrConditions->add($qb->expr()->in('j.publicationStatus', ':pubStatuses'));
                $qb->setParameter('pubStatuses', $pubStatuses);
            }

            if (!empty($actStatuses)) {
                $stateOrConditions->add($qb->expr()->in('j.activityStatus', ':actStatuses'));
                $qb->setParameter('actStatuses', $actStatuses);
            }

            $qb->andWhere($stateOrConditions);
        }

        // Filter : Search
        if (!empty($criteria['searchText'])) {
            $qb->andWhere('LOWER(j.title) LIKE :searchText')
            ->setParameter('searchText', '%' . mb_strtolower(trim($criteria['searchText'])) . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }


    
    #[Override]
    public function analyseJobOfferCollection(string $userId): JobOfferStatistics
    {
        $qb = $this->manager->createQueryBuilder();

        /** COUNT  */
        $qb->select([
                'COUNT(DISTINCT job.id) AS totalOffers',
                'SUM(CASE WHEN job.activityStatus = :activeStatus THEN 1 ELSE 0 END) AS activeOffers',
                'SUM(CASE WHEN job.activityStatus = :pendingStatus THEN 1 ELSE 0 END) AS pendingOffers',
                'SUM(CASE WHEN job.status = :closedStatus THEN 1 ELSE 0 END) AS closedOffers',
                'COUNT(DISTINCT app.id) AS applicationCount',
                'COUNT(DISTINCT view.id) AS viewCount',
                'COUNT(DISTINCT interview.startDate > NOW()) AS scheduledInterviews',
            ])
            ->from(JobOfferEntity::class, 'job')
            
            ->leftJoin('job.applications', 'app')
            ->leftJoin('job.views', 'view')
            ->leftJoin("job.interviews", "interview")
            ->where('job.user = :userId')
            
            ->setParameters([
                'userId' => $userId,
                'activeStatus' => JobActivityStatus::ACTIVE,
                'pendingStatus' => JobActivityStatus::PENDING,
                'closedStatus' => JobPublicationStatus::CLOSED,
            ]);
        
        $result = $qb->getQuery()->getSingleResult();

        $views = $result["viewCount"] ?? 0;
        $applications = $result["applicationCount"] ?? 0;

        $applicationRate = $views > 0
            ? ($applications / $views)
            : 0;

        return new JobOfferStatistics(
            totalOffers: $result["totalOffers"] ?? 0,
            viewCount: $result["viewCount"] ?? 0,
            applicationCount: $result["applicationCount"] ?? 0, //candidatures
            activeOffers: $result["activeOffers"] ?? 0,
            pendingReviewOffers: $result["pendingOffers"] ?? 0,
            closedOffers: $result["closedOffers"] ?? 0,
            applicationRate: $applicationRate,
            scheduledInterviews: $result["scheduledInterviews"] ?? 0,
        );
    }

    #[Override]
    public function fetchViewsGroupedByMonth(string $userId, string $jobId)
    {
        throw new \Exception('Not implemented');
    }



    /**
     * Convertit des valeurs scalaires/string issues de la QueryString en booléen PHP strict.
     */
    private function parseBooleanParam(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}