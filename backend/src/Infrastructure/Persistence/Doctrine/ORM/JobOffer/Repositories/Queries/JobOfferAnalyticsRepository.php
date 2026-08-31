<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Repositories\Queries;

use App\Application\Query\JobOffer\DTO\JobOfferStatistics;
use App\Application\Query\JobOffer\Repositories\JobOfferAnalyticsRepositoryInterface;
use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\EmploymentOffer\EmploymentOfferStatus;
use App\Domain\JobOffer\JobActivityStatus;
use App\Domain\JobOffer\JobOfferVisibilityStatus;
use App\Domain\JobOffer\JobPublicationStatus;


use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\EmploymentOffer\EmploymentOfferEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferViewEntity;
use App\Infrastructure\Utils\PercentageCalculator;


use Doctrine\ORM\EntityManagerInterface;
use Override;



class JobOfferAnalyticsRepository implements JobOfferAnalyticsRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $manager,
    ){}

    
    /**
     * Retrieves aggregated candidate/application statistics for a given job offer.
     *
     * @param string|null  $jobId If null, stats are calculated for all jobs belonging to the user
     * @return array{
     *      preselect: int,
     *      interviews: int,
     *      rejected: int,
     *      offer: int
     * }
     */
    public function getJobStats(string $userId ,?string $jobId = null): array
    {
        //  Optimized Breakdown of Applications by Status
        $qbStats = $this->manager->createQueryBuilder()
            ->select('
                SUM(CASE WHEN a.status = :preselect THEN 1 ELSE 0 END) AS preselect,
                SUM(CASE WHEN a.status = :rejected THEN 1 ELSE 0 END) AS rejected,
                SUM(CASE WHEN a.status = :offer THEN 1 ELSE 0 END) AS offer
            ')
            ->from(ApplicationEntity::class, 'a')
            ->setParameter('preselect', JobApplicationStatus::PRESELECTED)
            ->setParameter('rejected', JobApplicationStatus::REJECTED)
            ->setParameter('offer', JobApplicationStatus::OFFER_PENDING);

        $isJobIdProvided = $jobId != null;
        if($isJobIdProvided){
            $qbStats->where('a.jobOffer = :jobId')
                    ->setParameter('jobId', $jobId);
        }
        else{
            $qbStats->innerJoin("a.jobOffer", 'j')
                    ->where("j.user = :userId")
                    ->setParameter("userId", $userId);
        }

        $stats = $qbStats->getQuery()->getSingleResult();

        //  Breakdown of Interviews Related to the JobOffer
        $qbInterviews =  $this->manager
            ->createQueryBuilder()
            ->select('COUNT(i.id)')
            ->from(InterviewEntity::class, 'i')
            ->innerJoin('i.application', 'a');
        
        if($isJobIdProvided){
            $qbInterviews->where("a.jobOffer = :jobId")
                            ->setParameter("jobId", $jobId);
        }
        else{
            $qbInterviews->innerJoin('a.jobOffer', 'j')
                            ->where('j.user = :userId')
                            ->setParameter('userId', $userId);
        }

        $interviewCount = (int) $qbInterviews->getQuery()->getSingleScalarResult();
     
        return [
            'preselect'  => (int) ($stats['preselect'] ?? 0),
            'interviews' => $interviewCount,
            'rejected'   => (int) ($stats['rejected'] ?? 0),
            'offer'      => (int) ($stats['offer'] ?? 0),
        ];
    }

    

    #[Override]
    public function analyseJobOfferCollection(string $userId): JobOfferStatistics
    {
        $qb = $this->manager->createQueryBuilder();

        // GLOBAL STATS
        $qb->select([
                'COUNT(DISTINCT job.id) AS totalOffers',
                // Statuts d'activité
                'SUM(CASE WHEN job.activityStatus = :activeStatus THEN 1 ELSE 0 END) AS activeOffers',
                'SUM(CASE WHEN job.activityStatus = :pendingStatus THEN 1 ELSE 0 END) AS pendingOffers',
                // Statuts de publication
                'SUM(CASE WHEN job.publicationStatus = :publishedStatus THEN 1 ELSE 0 END) AS publishedOffers',
                'SUM(CASE WHEN job.publicationStatus = :draftStatus THEN 1 ELSE 0 END) AS draftOffers',
                'SUM(CASE WHEN job.publicationStatus = :closedStatus THEN 1 ELSE 0 END) AS closedOffers',
                // Statuts de visibilité
                'SUM(CASE WHEN job.visibilityStatus = :publicVisibility THEN 1 ELSE 0 END) AS publicOffers',
                'SUM(CASE WHEN job.visibilityStatus = :privateVisibility THEN 1 ELSE 0 END) AS privateOffers',
            ])
            ->from(JobOfferEntity::class, 'job')
            ->where('job.user = :userId')
            ->setParameters([
                'userId' => $userId,
                'activeStatus' => JobActivityStatus::ACTIVE,
                'pendingStatus' => JobActivityStatus::PENDING,
                'publishedStatus' => JobPublicationStatus::PUBLISHED,
                'draftStatus' => JobPublicationStatus::DRAFT,
                'closedStatus' => JobPublicationStatus::CLOSED,
                'publicVisibility' => JobOfferVisibilityStatus::PUBLIC,
                'privateVisibility' => JobOfferVisibilityStatus::PRIVATE,
            ]);


        $stats = $qb->getQuery()->getSingleResult();

        // Aggregated Applications Stats (Total, Hired, Rejected)
        $applicationStats = $this->manager->createQueryBuilder()
            ->select([
                'COUNT(app.id) AS totalCount',
                'SUM(CASE WHEN app.status = :hiredStatus THEN 1 ELSE 0 END) AS hiredCount',
                'SUM(CASE WHEN app.status = :rejectedStatus THEN 1 ELSE 0 END) AS rejectedCount',
            ])
            ->from(ApplicationEntity::class, 'app')
            ->innerJoin('app.jobOffer', 'job')
            ->where('job.user = :userId')
            ->setParameters([
                'userId' => $userId,
                'hiredStatus' => JobApplicationStatus::HIRED,
                'rejectedStatus' => JobApplicationStatus::REJECTED,
            ])
            ->getQuery()
            ->getSingleResult();


        $applicationCount = (int) ($applicationStats['totalCount'] ?? 0);
        $hiredApplicationCount = (int) ($applicationStats['hiredCount'] ?? 0);
        $rejectedApplicationCount = (int) ($applicationStats['rejectedCount'] ?? 0);
 
        // Job offer views
        $viewCount = (int) $this->manager->createQueryBuilder()
            ->select('COUNT(view.id)')
            ->from(JobOfferViewEntity::class, 'view')
            ->innerJoin('view.jobOffer', 'job')
            ->where('job.user = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();

        // Entretiens scheduled
        $scheduledInterviews = (int) $this->manager->createQueryBuilder()
            ->select('COUNT(interview.id)')
            ->from(InterviewEntity::class, 'interview')
            ->innerJoin('interview.application', 'a')
            ->innerJoin('a.jobOffer', 'job')
            ->where('job.user = :userId')
            ->andWhere('interview.startDate > CURRENT_TIMESTAMP()')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getSingleScalarResult();

        // Aggregated Employment Offers Stats (Total Generated, Total Accepted)
        $employmentOfferStats = $this->manager->createQueryBuilder()
            ->select([
                'COUNT(emp.id) AS totalGenerated',
                'SUM(CASE WHEN emp.status = :acceptedStatus THEN 1 ELSE 0 END) AS totalAccepted',
            ])
            ->from(EmploymentOfferEntity::class, 'emp')
            ->innerJoin('emp.application', 'app')
            ->innerJoin('app.jobOffer', 'job')
            ->where('job.user = :userId')
            ->setParameters([
                'userId' => $userId,
                'acceptedStatus' => EmploymentOfferStatus::ACCEPTED,
            ])
            ->getQuery()
            ->getSingleResult();

        $totalGeneratedEmploymentOffers = (int) ($employmentOfferStats['totalGenerated'] ?? 0);
        $totalAcceptedEmploymentOffers = (int) ($employmentOfferStats['totalAccepted'] ?? 0);

        // VARTION CALCULATION (% Increase This Week)
        $percentageCalculator = new PercentageCalculator();

        // Received application this week (1 join : e.jobOffer -> job)
        $appCounts = $this->getWeeklyComparisonCounts(
            ApplicationEntity::class,
            'appliedAt',
            $userId,
            joins: ['e.jobOffer' => 'job']
        );
        $applicationIncreaseThisWeek = $percentageCalculator->calculatePercentageIncrease($appCounts['current'], $appCounts['previous']);

        //-- Hired candidate this week
        $hiredAppCounts = $this->getWeeklyComparisonCounts(
            ApplicationEntity::class,
            'appliedAt',
            $userId,
            additionalFilter: fn($qb) => $qb->andWhere('e.status = :status')->setParameter('status', JobApplicationStatus::HIRED),
            joins: ['e.jobOffer' => 'job']
        );
        $hiredIncreaseThisWeek = $percentageCalculator->calculatePercentageIncrease($hiredAppCounts['current'], $hiredAppCounts['previous']);

        // Employment offer generated this week (2 join : e.application -> app, app.jobOffer -> job)
        $generatedEmpOfferCounts = $this->getWeeklyComparisonCounts(
            EmploymentOfferEntity::class,
            'createdAt',
            $userId,
            joins: [
                'e.application' => 'app',
                'app.jobOffer' => 'job',
            ]
        );
        $generatedOfferEmploymentIncreaseThisWeek = $percentageCalculator->calculatePercentageIncrease(
            $generatedEmpOfferCounts['current'],
            $generatedEmpOfferCounts['previous']
        );

        // Employment offer accepted this week
        $acceptedEmpOfferCounts = $this->getWeeklyComparisonCounts(
            EmploymentOfferEntity::class,
            'createdAt',
            $userId,
            additionalFilter: fn($qb) => $qb->andWhere('e.status = :status')->setParameter('status', EmploymentOfferStatus::ACCEPTED),
            joins: [
                'e.application' => 'app',
                'app.jobOffer' => 'job',
            ]
        );
        $acceptedOfferEmploymentIncreaseThisWeek = $percentageCalculator->calculatePercentageIncrease(
            $acceptedEmpOfferCounts['current'],
            $acceptedEmpOfferCounts['previous']
        );

        // Schedule Interviews this week (1 join : e.jobOffer -> job)
        $interviewCounts = $this->getWeeklyComparisonCounts(
            InterviewEntity::class,
            'createdAt',
            $userId,
            joins: [
                'e.application' => 'app',
                'app.jobOffer' => 'job',
            ]
        );
        $interviewsIncreaseThisWeek = $percentageCalculator->calculatePercentageIncrease($interviewCounts['current'], $interviewCounts['previous']);


        // DERIVATED CALCULS 
        $applicationRate = $viewCount > 0 ? ($applicationCount / $viewCount) : 0;


        //   DTO
        return new JobOfferStatistics(
            totalOffers: (int) ($stats['totalOffers'] ?? 0),
            viewCount: $viewCount,
            applicationCount: $applicationCount,
            rejectedApplicationCount: $rejectedApplicationCount,
            activeOffers: (int) ($stats['activeOffers'] ?? 0),
            pendingReviewOffers: (int) ($stats['pendingOffers'] ?? 0),
            publishedOffers: (int) ($stats['publishedOffers'] ?? 0),
            draftOffers: (int) ($stats['draftOffers'] ?? 0),
            closedOffers: (int) ($stats['closedOffers'] ?? 0),
            publicOffers: (int) ($stats['publicOffers'] ?? 0),
            privateOffers: (int) ($stats['privateOffers'] ?? 0),
            applicationRate: $applicationRate,
            scheduledInterviews: $scheduledInterviews,
            totalGeneratedEmploymentOffers: $totalGeneratedEmploymentOffers,
            totalAcceptedEmploymentOffers: $totalAcceptedEmploymentOffers,
            applicationIncreaseThisWeek: $applicationIncreaseThisWeek,
            generatedOfferEmploymentIncreaseThisWeek: $generatedOfferEmploymentIncreaseThisWeek,
            acceptedOfferEmploymentIncreaseThisWeek: $acceptedOfferEmploymentIncreaseThisWeek,
            interviewsIncreaseThisWeek: $interviewsIncreaseThisWeek,
            hiredApplicationCount: $hiredApplicationCount,
            hiredIncreaseThisWeek: $hiredIncreaseThisWeek,
        );
    }



    /**
     * Returns weekly entity counts for the current and previous week.
     *
     * @param class-string $entityClass The entity class to count.
     * @param string $dateField The entity field used to determine the creation date.
     * @param string $userId The ID of the user used to filter the results.
     * @param \Closure|null $additionalFilter Optional callback to apply additional query filters.
     * @param array<string, string> $joins Map of joins to execute, e.g. ['e.application' => 'app', 'app.jobOffer' => 'job']
     *
     * @return array{current: int, previous: int}
     */
    private function getWeeklyComparisonCounts(
        string $entityClass,
        string $dateField,
        string $userId,
        ?\Closure $additionalFilter = null,
        array $joins = ['e.jobOffer' => 'job']
    ): array {
        $now = new \DateTimeImmutable();
        $sevenDaysAgo = $now->modify('-7 days');
        $fourteenDaysAgo = $now->modify('-14 days');

        $qb = $this->manager->createQueryBuilder()
            ->select([
                "SUM(CASE WHEN e.{$dateField} >= :sevenDaysAgo THEN 1 ELSE 0 END) AS currentWeek",
                "SUM(CASE WHEN e.{$dateField} >= :fourteenDaysAgo AND e.{$dateField} < :sevenDaysAgo THEN 1 ELSE 0 END) AS previousWeek"
            ])
            ->from($entityClass, 'e');

        // Application dynamique de la chaîne de jointures
        foreach ($joins as $from => $alias) {
            $qb->innerJoin($from, $alias);
        }

        $qb->where('job.user = :userId')
            ->andWhere("e.{$dateField} >= :fourteenDaysAgo")
            ->setParameters([
                'userId' => $userId,
                'sevenDaysAgo' => $sevenDaysAgo,
                'fourteenDaysAgo' => $fourteenDaysAgo,
            ]);

        if ($additionalFilter !== null) {
            $additionalFilter($qb);
        }

        $result = $qb->getQuery()->getSingleResult();

        return [
            'current' => (int) ($result['currentWeek'] ?? 0),
            'previous' => (int) ($result['previousWeek'] ?? 0),
        ];
    }




    #[Override]
    public function fetchViewsGroupedByMonth(string $userId, string $jobId)
    {
        throw new \Exception('Not implemented');
    }
}