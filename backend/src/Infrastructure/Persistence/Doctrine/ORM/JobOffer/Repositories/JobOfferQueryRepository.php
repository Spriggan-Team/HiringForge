<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Repositories;

use App\Application\Query\JobOffer\DTO\JobOfferStatistics;
use App\Application\Query\JobOffer\DTO\JobOfferListItem;
use App\Application\Query\JobOffer\JobOfferQueryRepositoryInterace;
use App\Domain\JobOffer\JobActivityStatus;
use App\Domain\JobOffer\JobPublicationStatus;

use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
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
    public function __construct(private EntityManagerInterface $manager){}


    public function fetchJobOfferViewById(
        string $userId,
        string $offerId
    ): JobOfferListItem
    {
        throw new \Exception('Not implemented');
    }


    public function fetchJobOfferViewCollection(
        ?string $userId = null,
        ?int $limit = null,
        ?int $skip = null,
        ?JobPublicationStatus $category = JobPublicationStatus::PUBLISHED
    ): array
    {
        throw new \Exception('Not implemented');
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
}