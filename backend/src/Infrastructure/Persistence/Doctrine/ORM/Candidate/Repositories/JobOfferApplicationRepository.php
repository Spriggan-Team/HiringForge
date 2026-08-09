<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate\Repositories;

use App\Domain\JobOffer\JobOfferRepositoryInterface;
use App\Domain\Exception\ApplicationNotFoundException;
use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use DomainException;
use Override;



class JobOfferApplicationRepository
    extends ServiceEntityRepository
    implements ApplicationRepositoryInterface
{

    public function __construct(
        ManagerRegistry $registry,
        JobOfferRepositoryInterface $jobRepository
    )
    {
        return parent::__construct($registry, ApplicationEntity::class);
    }

    /**
     * Asserts that an application exists by its ID.
     *
     * @throws ApplicationNotFoundException|\DomainException If the application does not exist.
     */
    public function assertExists(string $id): void
    {
        $exists = (bool) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();

        if (!$exists) {
            throw new \DomainException("Job application with ID '$id' was not found.");
        }
    }

    //--------------------------------------
    //---------- Count & Stats
    //---------------------------------------

    /** 
     * Counts applications matching criteria. 
     * If no status is provided, all applications are counted.
     *
     * @param array{
     *     companyId?: string,
     *     jobOfferId?: string,
     *     userId?: string,
     *     status?: mixed
     * } $criteria
     */
    #[Override]
    public function count(array $criteria = []): int 
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(DISTINCT a.id)');

        $hasJobOfferJoin = false;

        if (!empty($criteria['jobOfferId'])) {
            $qb->andWhere('a.jobOffer = :jobOfferId')
               ->setParameter('jobOfferId', $criteria['jobOfferId']);
        }

        if (!empty($criteria['status'])) {
            $qb->andWhere('a.status = :status')
               ->setParameter('status', $criteria['status']);
        }

        if (!empty($criteria['companyId'])) {
            if (!$hasJobOfferJoin) {
                $qb->innerJoin('a.jobOffer', 'j');
                $hasJobOfferJoin = true;
            }
            $qb->andWhere('j.company = :companyId') 
               ->setParameter('companyId', $criteria['companyId']);
        }

        if (!empty($criteria['userId'])) {
            if (!$hasJobOfferJoin) {
                $qb->innerJoin('a.jobOffer', 'j');
                $hasJobOfferJoin = true;
            }
            $qb->andWhere('j.user = :userId')
               ->setParameter('userId', $criteria['userId']);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }


    
    /**
     * Counts applications created within a specific date range.
     */
    public function countApplicationsInPeriod(string $jobOfferId, \DateTimeInterface $start, \DateTimeInterface $end): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.jobOffer = :jobOfferId')
            ->andWhere('a.appliedAt >= :start')
            ->andWhere('a.appliedAt <= :end')
            ->setParameter('jobOfferId', $jobOfferId)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();
    }



    /**
     * Calculates average time to hire in days for a specific job offer and user.
     */
    public function getAvgTimeToHireDays(string $jobOfferId, string $userId): int
    {
        $result = $this->createQueryBuilder('a')
            ->select('AVG(DATE_DIFF(a.updatedAt, a.appliedAt))')
            ->innerJoin('a.jobOffer', 'j')
            ->where('j.id = :jobOfferId')
            ->andWhere('j.user = :userId')
            ->andWhere('a.status = :status')
            ->setParameter('jobOfferId', $jobOfferId)
            ->setParameter('userId', $userId)
            ->setParameter('status', JobApplicationStatus::HIRED)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (int) round((float) $result) : 0;
    }

    /**
     * Calculates baseline average time to hire across ALL job offers owned by the user
     * to compute the relative difference (+/- days vs user average).
     */
    public function getUserAvgTimeToHireDays(string $userId): int
    {
        $result = $this->createQueryBuilder('a')
            ->select('AVG(DATE_DIFF(a.updatedAt, a.appliedAt))')
            ->innerJoin('a.jobOffer', 'j')
            ->where('j.user = :userId')
            ->andWhere('a.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', JobApplicationStatus::HIRED)
            ->getQuery()
            ->getSingleScalarResult();

        return $result !== null ? (int) round((float) $result) : 0;
    }



    /**
     * Retrieves the number of applications made by candidates for a job offer or all jobs of a user within a timeframe.
     *
     * @param string $userId The recruiter ID (verifies ownership/relation)
     * @param string|null $jobId  The unique job offer ID
     * @param string $timeframe 'month' (12 items) or 'week' (7 items)
     * @return float[] List of postulation counts ordered chronologically
     */
    public function getPostulationMetrics(string $userId, ?string $jobId = null, string $timeframe = 'month'): array
    {
        $now = new \DateTimeImmutable();

        if ($timeframe === 'week') {
            return $this->getWeeklyMetrics($userId, $jobId, $now);
        }

        return $this->getMonthlyMetrics($userId, $jobId, $now);
    }



    /**
     * Generates 12 elements for the current year (Jan to Dec).
     * 
     * @return float[]
     */
    public function getMonthlyMetrics(string $userId, ?string $jobId, \DateTimeImmutable $now): array
    {
        // Set the 12 months to 0.0 (indexed from 1 to 12)
        $metrics = array_fill(1, 12, 0.0);

        $startOfYear = $now->setDate((int) $now->format('Y'), 1, 1)->setTime(0, 0, 0);
        $endOfYear   = $now->setDate((int) $now->format('Y'), 12, 31)->setTime(23, 59, 59);

        // Monthly Aggregation Requests
        $qb = $this->createQueryBuilder('a')
            ->select('MONTH(a.appliedAt) as period', 'COUNT(a.id) as count')
            ->innerJoin('a.jobOffer', 'j')
            ->where('j.user = :userId')
            ->andWhere('a.appliedAt BETWEEN :start AND :end')
            ->setParameter('userId', $userId)
            ->setParameter('start', $startOfYear)
            ->setParameter('end', $endOfYear)
            ->groupBy('period');

        if ($jobId !== null) {
            $qb->andWhere('j.id = :jobId')
               ->setParameter('jobId', $jobId);
        }

        $results = $qb->getQuery()->getResult();

        //  Enter the actual results
        foreach ($results as $row) {
            $monthIndex = (int) $row['period'];
            $metrics[$monthIndex] = (float) $row['count'];
        }

        return array_values($metrics); // return array of items (0-11)
    }


    /**
     * Generates 7 elements for the current week (Mon to Sun).
     * 
     * @return float[]
     */
    public function getWeeklyMetrics(string $userId, ?string $jobId, \DateTimeImmutable $now): array
    {
        //  Set the 7 days to 0.0 (Monday through Sunday)
        $metrics = array_fill(1, 7, 0.0);

        $startOfWeek = $now->modify('monday this week')->setTime(0, 0, 0);
        $endOfWeek   = $now->modify('sunday this week')->setTime(23, 59, 59);

        // Aggregation query by day of the week
        $qb = $this->createQueryBuilder('a')
            ->select('WEEKDAY(a.appliedAt) + 1 as period', 'COUNT(a.id) as count')
            ->innerJoin('a.jobOffer', 'j')
            ->where('j.user = :userId')
            ->andWhere('a.appliedAt BETWEEN :start AND :end')
            ->setParameter('userId', $userId)
            ->setParameter('start', $startOfWeek)
            ->setParameter('end', $endOfWeek)
            ->groupBy('period');
        
        if ($jobId !== null) {
            $qb->andWhere('j.id = :jobId')
               ->setParameter('jobId', $jobId);
        }

        $results = $qb->getQuery()->getResult();

        // Enter the actual results
        foreach ($results as $row) {
            $dayIndex = (int) $row['period'];
            $metrics[$dayIndex] = (float) $row['count'];
        }

        return array_values($metrics); //r teurn an array (index 0-6)
    }

    //-------------------------------------
    //--------- Entity & Collection fetch 
    //------------------------------------

 
    #[Override]
    public function fetchJobApplicationsProjection(
        ?string $jobId,
        int $limit = 17,
        int $skip = 0,
        array $scheme = ['id' => true],
        ?string $userId = null,
        ?string $companyId = null,
        ?string $search = null,
        ?JobApplicationStatus $status = null,
    ): array {
        $qb = $this->createQueryBuilder('a');
        $selectedFields = [];

        // 1. Dynamic selection of Application fields
        $allowedApplicationFields = ['id', 'matchScore', 'appliedAt', 'status'];
        foreach ($allowedApplicationFields as $field) {
            if (!empty($scheme[$field])) {
                $selectedFields[] = 'a.' . $field;
            }
        }

        // Check scheme dependencies
        $hasCandidateScheme = !empty($scheme['candidate']) && is_array($scheme['candidate']);
        $hasJobOfferScheme = !empty($scheme['jobOffer']) && is_array($scheme['jobOffer']);

        if (empty($selectedFields) && !$hasCandidateScheme && !$hasJobOfferScheme) {
            $selectedFields[] = 'a.id';
        }

        // --- JOINTURE CANDIDAT ---
        $needsCandidateJoin = $hasCandidateScheme || !empty($search);
        if ($needsCandidateJoin) {
            $qb->leftJoin('a.candidate', 'c');
        }

        // --- JOINTURE JOB OFFER ---
        // On effectue la jointure si demandée par le scheme OU si userId est présent
        $needsJobOfferJoin = $hasJobOfferScheme || ($userId !== null && $companyId === null);
        if ($needsJobOfferJoin) {
            $qb->innerJoin('a.jobOffer', 'jo');
        }

        // 2. Handle Candidate sub-projection
        if ($hasCandidateScheme) {
            $allowedCandidateFields = ['id', 'firstName', 'lastName', 'email'];

            foreach ($allowedCandidateFields as $candField) {
                if (!empty($scheme['candidate'][$candField])) {
                    $selectedFields[] = 'c.' . $candField . ' AS candidate_' . $candField;
                }
            }

            // Candidate image scheme
            if (!empty($scheme['candidate']['image'])) {
                $qb->leftJoin('c.image', 'img');
                $imageScheme = $scheme['candidate']['image'];

                if ($imageScheme === true) {
                    $selectedFields[] = 'img.name AS candidate_image_name';
                } elseif (is_array($imageScheme)) {
                    $allowedImageFields = ['name', 'mime', 'size', 'createdAt'];
                    foreach ($allowedImageFields as $imgField) {
                        if (!empty($imageScheme[$imgField])) {
                            $selectedFields[] = 'img.' . $imgField . ' AS candidate_image_' . $imgField;
                        }
                    }
                }
            }
        }

        //  Handle JobOffer sub-projection 
        if ($hasJobOfferScheme) {
            $allowedJobFields = ['id', 'title'];
            foreach ($allowedJobFields as $jobField) {
                if (!empty($scheme['jobOffer'][$jobField])) {
                    $selectedFields[] = 'jo.' . $jobField . ' AS jobOffer_' . $jobField;
                }
            }
        }

        $qb->select(implode(', ', $selectedFields));

        // 3. Dynamic filtering based on jobId, companyId, and userId
        if ($jobId !== null) {
            $qb->andWhere('a.jobOffer = :jobId')
                ->setParameter('jobId', $jobId);
        }

        if ($companyId !== null) {
            $qb->andWhere('a.company = :companyId')
                ->setParameter('companyId', $companyId);
        }
        elseif ($userId !== null) {
            $qb->andWhere('jo.user = :userId')
                ->setParameter('userId', $userId);
        }

        // 4. Status filter
        if ($status !== null) {
            $qb->andWhere('a.status = :status')
                ->setParameter('status', $status);
        }

        // 5. Search filter (by candidate first name, last name or full name)
        if (!empty($search)) {
            $trimmedSearch = trim($search);
            $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('c.firstName', ':search'),
                    $qb->expr()->like('c.lastName', ':search'),
                    $qb->expr()->like("CONCAT(c.firstName, ' ', c.lastName)", ':search')
                )
            )->setParameter('search', '%' . $trimmedSearch . '%');
        }

        // 6. Pagination
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }
        if ($skip > 0) {
            $qb->setFirstResult($skip);
        }

        $results = $qb->getQuery()->getArrayResult();

        // 7. Restructure output array (MAJ pour gérer à la fois candidate et jobOffer)
        if ($hasCandidateScheme || $hasJobOfferScheme) {
            return array_map(static function (array $row) use ($hasCandidateScheme, $hasJobOfferScheme) {
                $candidateData = [];
                $imageData = [];
                $jobOfferData = [];
                $hasCandidateValue = false;
                $hasJobOfferValue = false;

                foreach ($row as $key => $value) {
                    if (str_starts_with($key, 'jobOffer_')) {
                        $realJobKey = str_replace('jobOffer_', '', $key);
                        $jobOfferData[$realJobKey] = $value;
                        if ($value !== null) {
                            $hasJobOfferValue = true;
                        }
                        unset($row[$key]);
                    } elseif (str_starts_with($key, 'candidate_image_')) {
                        $realImgKey = str_replace('candidate_image_', '', $key);
                        $imageData[$realImgKey] = $value;
                        unset($row[$key]);
                    } elseif (str_starts_with($key, 'candidate_')) {
                        $realKey = str_replace('candidate_', '', $key);
                        $candidateData[$realKey] = $value;
                        if ($value !== null) {
                            $hasCandidateValue = true;
                        }
                        unset($row[$key]);
                    }
                }

                // Reconstruction de l'objet Candidate
                if ($hasCandidateScheme) {
                    if (!empty($imageData) && array_filter($imageData, static fn($v) => $v !== null)) {
                        $candidateData['image'] = $imageData;
                    }
                    $row['candidate'] = $hasCandidateValue ? $candidateData : null;
                }

                // Reconstruction de l'objet JobOffer
                if ($hasJobOfferScheme) {
                    $row['jobOffer'] = $hasJobOfferValue ? $jobOfferData : null;
                }

                return $row;
            }, $results);
        }

        return $results;
    }



    public function changeStatus(string $applicationId, JobApplicationStatus $newStatus): void
    {
        $application = $this->getEntityManager()->getReference(ApplicationEntity::class, $applicationId);
        if(!$application){
            throw new DomainException("Undentified application, it does not exits in the storage : " . $applicationId);
        }
        $currentStatus = $application->getStatus();

        if (!$currentStatus->canTransitionTo($newStatus)) {
            throw new DomainException(sprintf(
                'Transition non autorisée du statut "%s" vers "%s".',
                $currentStatus->value,
                $newStatus->value
            ));
        }

        // Mise à jour effective
        $application->setStatus($newStatus);
        
        $this->entityManager->persist($application);
        $this->entityManager->flush();
    }

}