<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate\Repositories;

use App\Api\Responder\ApiResponse;
use App\Domain\Candidate\Application\Application;
use App\Domain\JobOffer\JobOfferRepositoryInterface;
use App\Domain\Exception\ApplicationNotFoundException;

use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\Candidate\Application\Pipeline\PipelineStageCriteria;
use App\Domain\Candidate\Application\Pipeline\PipelineStageCriteriaResolver;
use App\Domain\Candidate\Application\Repositories\ApplicationContext;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\Candidate\Application\Repositories\CandidateApplication;
use App\Domain\Candidate\Application\Pipeline\PipelineStageType;
use App\Domain\Candidate\CandidateLightModel;

use App\Domain\File\StaticMedia;
use App\Domain\Interviews\InterviewStatus;
use App\Domain\Interviews\InterviewType;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateResumeEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;


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

    #[Override]
    public function assertRecruiterHasAccessToApplicationCollection(string $recruiterId, array $applicationIds): void
    {
        $count = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->innerJoin('a.jobOffer', 'job')
            ->where('a.id IN (:applicationIds)')
            ->andWhere('job.user = :recruiterId')
            ->setParameter('applicationIds', $applicationIds)
            ->setParameter('recruiterId', $recruiterId)
            ->getQuery()
            ->getSingleScalarResult();

        if ((int) $count !== count($applicationIds)) {
            throw new \DomainException(
                'The recruiter does not have access to all applications.'
            );
        }
    }


    #[Override]
    public function assertApplicationBelongsToCandidate(string $candidateId, string $applicationId): void
    {
        $relation = $this->createQueryBuilder('a')
            ->select('1')
            ->where('a.candidate = :candidateId')
            ->andWhere('a.id = :applicationId')
            ->setParameter('candidateId', $candidateId)
            ->setParameter('applicationId', $applicationId)
            ->getQuery()
            ->getOneOrNullResult();

        if ($relation === null) {
            throw new \DomainException(
                'The current candidate is not related to the designated application.'
            );
        }
    }



    #[Override]
    public function assertRecruiterHasAccessToApplication(
        string $recruiterId,
        string $applicationId,
        ?string $candidateId = null
    ): void {
        $queryBuilder = $this->createQueryBuilder('a')
            ->select('1')
            ->innerJoin('a.jobOffer', 'job')
            ->where('a.id = :applicationId')
            ->andWhere('job.user = :recruiterId')
            ->setParameter('applicationId', $applicationId)
            ->setParameter('recruiterId', $recruiterId);

        if ($candidateId !== null) {
            $queryBuilder
                ->andWhere('a.candidate = :candidateId')
                ->setParameter('candidateId', $candidateId);
        }

        $relation = $queryBuilder
            ->getQuery()
            ->getOneOrNullResult();

        if ($relation === null) {
            throw new \DomainException(
                'The recruiter does not have access to this application.'
            );
        }
    }


    
    #[Override]
    public function hasApplicationsUsingResume(string $candidateId, string $resumeId): bool
    {
        $count = (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.candidate = :candidateId')
            ->andWhere('a.candidateResume = :resumeId')
            ->setParameters([
                'candidateId' => $candidateId,
                'resumeId' => $resumeId,
            ])
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }




    #[Override]
    public function getCandidatesPipeline(string $recruiterId, int $skip, int $limit): array
    {
        return [
            $this->getCandidatesForStage(
                stageType: PipelineStageType::APPLIED,
                recruiterId: $recruiterId, 
                skip: $skip, limit: $limit,
            ),
            $this->getCandidatesForStage(
                stageType:  PipelineStageType::RH_INTERVIEWS,
                recruiterId: $recruiterId, 
                skip: $skip, limit: $limit,
            ),
            $this->getCandidatesForStage(
                stageType: PipelineStageType::TECHNICAL_INTERVIEWS,
                recruiterId: $recruiterId, 
                skip: $skip, limit: $limit,
            ),
            $this->getCandidatesForStage(
                recruiterId: $recruiterId,
                stageType: PipelineStageType::HIRED,
                skip: $skip, limit: $limit,
            ),
        ];
    }
    

    /**
     * Retrieve paginated candidates for a specific pipeline stage
     * @param array<int,JobApplicationStatus> $appStatuses
     */
    public function getCandidatesForStage(
        PipelineStageType $stageType,
        string $recruiterId,
        int $skip,
        int $limit,
    ): array {
        $resolver = new PipelineStageCriteriaResolver();
        $criteria = $resolver->resolve($stageType);
        $hasInterviewFilter = $criteria->hasInterviewFilter();

        // 1. Fetch distinct candidates
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select(
                'c.id AS candidateId',
                'c.firstName',
                'c.lastName',
                'c.email',
                'MAX(addr.id) AS addressId',
                'MAX(addr.city) AS city',
                'MAX(addr.country) AS country',
                'MAX(addr.postalCode) AS postalCode',
                'MAX(addr.street) AS street',
                'MAX(img.id) AS imageId',
                $hasInterviewFilter ? 'MAX(i.createdAt) AS stageCreatedAt' : 'MAX(a.appliedAt) AS stageCreatedAt'
            )
            ->from(CandidateEntity::class, 'c')
            ->innerJoin('c.applications', 'a')
            ->innerJoin('a.jobOffer', 'j')
            ->leftJoin('c.address', 'addr')
            ->leftJoin('c.image', 'img')
            ->where('j.user = :recruiterId')
            ->andWhere('a.status IN (:appStatuses)')
            ->groupBy('c.id')
            ->setParameters([
                'recruiterId' => $recruiterId,
                'appStatuses' => $criteria->appStatuses,
            ]);

        if ($hasInterviewFilter) {
            $qb->innerJoin('a.interviews', 'i');

            if ($criteria->interviewStatus !== null) {
                $qb->andWhere('i.status = :interviewStatus')
                ->setParameter('interviewStatus', $criteria->interviewStatus);
            }

            if ($criteria->interviewType !== null) {
                $qb->andWhere('i.type = :interviewType')
                ->setParameter('interviewType', $criteria->interviewType);
            }
        }

        $results = $qb->setFirstResult($skip)
            ->setMaxResults($limit)
            ->getQuery()
            ->getArrayResult();

        // 2. Count total distinct candidates
        $countQb = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(DISTINCT c.id)')
            ->from(CandidateEntity::class, 'c')
            ->innerJoin('c.applications', 'a')
            ->innerJoin('a.jobOffer', 'j')
            ->where('j.user = :recruiterId')
            ->andWhere('a.status IN (:appStatuses)')
            ->setParameters([
                'recruiterId' => $recruiterId,
                'appStatuses' => $criteria->appStatuses,
            ]);

        if ($hasInterviewFilter) {
            $countQb->innerJoin('a.interviews', 'i');

            if ($criteria->interviewStatus !== null) {
                $countQb->andWhere('i.status = :interviewStatus')
                        ->setParameter(
                            'interviewStatus',
                            $criteria->interviewStatus
                        );
            }

            if ($criteria->interviewType !== null) {
                $countQb->andWhere('i.type = :interviewType')
                        ->setParameter(
                            'interviewType',
                            $criteria->interviewType
                        );
            }
        }

        $totalCount = (int) $countQb->getQuery()->getSingleScalarResult();

        // 3. Map to DTOs
        $now = new \DateTimeImmutable();
        $candidates = [];

        foreach ($results as $row) {
            $address = array_filter([
                'id' => $row['addressId'],
                'city' => $row['city'],
                'country' => $row['country'],
                'postalCode' => $row['postalCode'],
                'street' => $row['street'],
            ]);

            $stageCreatedAt = $row['stageCreatedAt'];
            $delayInSec = 0;

            if ($stageCreatedAt !== null) {
                $date = $stageCreatedAt instanceof \DateTimeInterface 
                    ? $stageCreatedAt 
                    : new \DateTimeImmutable($stageCreatedAt);
                
                $delayInSec = max(0, $now->getTimestamp() - $date->getTimestamp());
            }

            $candidates[] = new CandidateLightModel(
                id: $row['candidateId'],
                firstName: $row['firstName'],
                lastName: $row['lastName'],
                email: $row['email'],
                address: $address,
                imageId: $row['imageId'],
                delayInSec: $delayInSec
            );
        }

        $more = max(0, $totalCount - ($skip + count($candidates)));

        return [
            'stageType' => $stageType,
            'candidates' => $candidates,
            'more' => $more,
        ];
    }


    #[Override]
    public function getApplicationContext(string $applicationId): ApplicationContext
    {
        $data = $this->createQueryBuilder('a')
            ->select(
                'a.id AS applicationId',
                'IDENTITY(a.candidate) AS candidateId',
                'j.title AS jobTitle',
                'c.name AS companyName',
                'u.id AS recruiterId' 
            )
            ->innerJoin('a.company', 'c')
            ->innerJoin('a.jobOffer', 'j')
            ->innerJoin('j.user', 'u')
            ->where('a.id = :applicationId')
            ->setParameter('applicationId', $applicationId)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$data) {
            throw new \DomainException("Application with ID {$applicationId} was not found.");
        }

        return new ApplicationContext(
            applicationId: $data['applicationId'],
            candidateId: $data['candidateId'],
            jobTitle: $data['jobTitle'],
            companyName: $data['companyName'],
            recruiterId: $data['recruiterId']
        );
    }


    public function getApplicationIdentity(string $applicationId): ?array
    {
        return $this->createQueryBuilder('a')
            ->select(
                'a.id AS applicationId',
                'IDENTITY(a.candidate) AS candidateId',
                'IDENTITY(a.company) AS companyId',
                'IDENTITY(j.user) AS recruiterId'
            )
            ->innerJoin('a.jobOffer', 'j')
            ->where('a.id = :id')
            ->setParameter('id', $applicationId)
            ->getQuery()
            ->getOneOrNullResult();
    }


    /**
     * Get candidate's identity
     * 
     * @return array{
     *  id: string,
     *  lastName: string,
     *  firstName: string
     * }|null
     */
    #[Override]
    public function getCandidateIdentity(string $applicationId): ?array
    {
        return $this->createQueryBuilder('a')
            ->select(
                'c.id AS id',
                'c.lastName AS lastName',
                'c.firstName AS firstName'
            )
            ->innerJoin('a.candidate', 'c')
            ->where('a.id = :applicationId')
            ->setParameter('applicationId', $applicationId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    
    #[Override]
    public function getApplicationStatus(string $applicationId): JobApplicationStatus
    {
        $status = $this->createQueryBuilder('a')
            ->select('a.status')
            ->where('a.id = :applicationId')
            ->setParameter('applicationId', $applicationId)
            ->getQuery()
            ->getSingleScalarResult();

        return JobApplicationStatus::from($status);
    }


    #[Override]
    public function getStatusesByIds(array $applicationIds): array
    {
        if ($applicationIds === []) {
            return [];
        }

        $result = $this->createQueryBuilder('a')
            ->select('a.id, a.status')
            ->where('a.id IN (:applicationIds)')
            ->setParameter('applicationIds', $applicationIds)
            ->getQuery()
            ->getArrayResult();

        $response = [];

        foreach ($result as $row) {
            $status = $row['status'];
            
            $response[$row['id']] = $status instanceof JobApplicationStatus 
                ? $status 
                : JobApplicationStatus::from($status);
        }

        return $response;
    }


    #[Override]
    public function getResumeFile(
        string $applicationId,
        string $candidateId
    ): StaticMedia {
        $result = $this->createQueryBuilder('a')
            ->select(
                'file.id',
                'file.name',
                'file.mime',
                'file.size',
                'file.originalName',
                'file.createdAt'
            )
            ->where('a.id = :applicationId')
            ->andWhere('a.candidate = :candidateId')
            ->setParameter('applicationId', $applicationId)
            ->setParameter('candidateId', $candidateId)
            ->innerJoin('a.candidateResume', 'cr')
            ->innerJoin('cr.file', 'file')
            ->getQuery()
            ->getOneOrNullResult();

        if ($result === null) {
            throw new \DomainException(
                'Resume file was not found for this application.'
            );
        }

        return StaticMedia::hydrate(
            id: $result['id'],
            name: $result['name'],
            mime: $result['mime'],
            size: $result['size'],
            originalName: $result['originalName'],
            createdAt: $result['createdAt'],
        );
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
    public function countApplications(array $criteria = []): int 
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
     * Counts applications created within a specific date range, optionally filtered by job offer.
     */
    public function countApplicationsInPeriod(
        string $userId,
        \DateTimeInterface $start,
        \DateTimeInterface $end,
        ?string $jobOfferId = null
    ): int
    {
        $qb = $this->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->innerJoin('a.jobOffer', 'j')
                ->where('j.user = :userId')
                ->andWhere('a.appliedAt >= :start')
                ->andWhere('a.appliedAt <= :end')
                ->setParameter('userId', $userId)
                ->setParameter('start', $start)
                ->setParameter('end', $end);

        if ($jobOfferId !== null) {
            $qb->andWhere('j.id = :jobOfferId')
            ->setParameter('jobOfferId', $jobOfferId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }


    /**
     * Calculates average time to hire in days for a specific job offer OR all user jobs.
     */
    public function getAvgTimeToHireDays(string $userId, ?string $jobOfferId =null): int
    {
        $qb = $this->createQueryBuilder('a')
                ->select('AVG(DATE_DIFF(a.updatedAt, a.appliedAt))')
                ->innerJoin('a.jobOffer', 'j')
                ->where('j.user = :userId')
                ->andWhere('a.status = :status')
                ->setParameter('userId', $userId)
                ->setParameter('status', JobApplicationStatus::HIRED);

        if ($jobOfferId !== null) {
            $qb->andWhere('j.id = :jobOfferId')
            ->setParameter('jobOfferId', $jobOfferId);
        }

        $result = $qb->getQuery()->getSingleScalarResult();
        
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
    public function findCandidateApplicationsBySearchTerm(string $recruiterId, string $query): array
    {
        $qb = $this->createQueryBuilder('a')
            // Direct instantiation of the DTO in the DQL query
            ->select(sprintf(
                'NEW %s(
                    a.id,
                    c.id,
                    c.firstName,
                    c.lastName,
                    j.id,
                    c.email,
                    j.title
                )',
                CandidateApplication::class
            ))
            ->innerJoin('a.candidate', 'c')
            ->innerJoin('a.jobOffer', 'j')
            ->innerJoin('j.user', 'u')
            ->where('u.id = :recruiterId')
            ->setParameter('recruiterId', $recruiterId);

        // Apply the search filter, if provided
        $cleanQuery = trim($query);
        if ($cleanQuery !== '') {
            $qb->andWhere(
                $qb->expr()->orX(
                    'LOWER(c.firstName) LIKE :term',
                    'LOWER(c.lastName) LIKE :term',
                    'LOWER(j.title) LIKE :term',
                    "LOWER(CONCAT(c.firstName, ' ', c.lastName)) LIKE :term"
                )
            )->setParameter('term', '%' . mb_strtolower($cleanQuery) . '%');
        }

        ApiResponse::$logger->error("Query : " . $cleanQuery);

        return $qb->getQuery()->getResult();
    }


 
    #[Override]
    public function fetchJobApplicationsProjection(
        ?string $jobId, 
        int $limit = 17, 
        int $skip = 0, 
        array $scheme = ['id'=>true], 
        ?string $userId =null, 
        ?string $companyId = null,
        ?array $appStatuses = null,
        ?string $search = null,
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
        $hasCandidateScheme  = !empty($scheme['candidate']) && is_array($scheme['candidate']);
        $hasJobOfferScheme   = !empty($scheme['jobOffer']) && is_array($scheme['jobOffer']);
        $hasInterviewsScheme = !empty($scheme['interviews']) && is_array($scheme['interviews']);

        if (!in_array('a.id', $selectedFields, true)) {
            $selectedFields[] = 'a.id';
        }

        // --- JOINTURE CANDIDAT ---
        $needsCandidateJoin = $hasCandidateScheme || !empty($search);
        if ($needsCandidateJoin) {
            $qb->leftJoin('a.candidate', 'c');
        }

        // --- JOINTURE JOB OFFER ---
        $needsJobOfferJoin = $hasJobOfferScheme || $userId !== null;
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
                }
                elseif (is_array($imageScheme)) {
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

        if ($hasInterviewsScheme) {
            $qb->leftJoin('a.interviews', 'i');
            $allowedInterviewFields = ['id', 'startDate', 'minutes', 'type', 'status'];
            foreach ($allowedInterviewFields as $iField) {
                if (!empty($scheme['interviews'][$iField])) {
                    $selectedFields[] = 'i.' . $iField . ' AS interview_' . $iField;
                }
            }
        }

        $qb->select(implode(', ', $selectedFields));

        // Dynamic filtering based on jobId, companyId, and userId
        if ($jobId !== null) {
            $qb->andWhere('a.jobOffer = :jobId')
            ->setParameter('jobId', $jobId);
        }

        if ($companyId !== null) {
            $qb->andWhere('a.company = :companyId')
            ->setParameter('companyId', $companyId);
        }

        if ($userId !== null) {
            $qb->andWhere('jo.user = :userId')
            ->setParameter('userId', $userId);
        }

        // Status filter
        if (!empty($appStatuses)) {
            $qb->andWhere('a.status IN (:appStatuses)')
            ->setParameter('appStatuses', $appStatuses);
        }

        //  Search filter (by candidate first name, last name or full name)
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

        // Pagination
        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }
        if ($skip > 0) {
            $qb->setFirstResult($skip);
        }

        $results = $qb->getQuery()->getArrayResult();

        // Restructure output array (MAJ pour gérer à la fois candidate et jobOffer)
        if ($hasCandidateScheme || $hasJobOfferScheme || $hasInterviewsScheme) {
            $groupedResults = [];

            foreach ($results as $row) {
                $appId = $row['id'] ?? null;
                if ($appId === null) {
                    continue;
                }

                //-- Find candidature first time
                if (!isset($groupedResults[$appId])) {
                    $groupedResults[$appId] = [];
                    $candidateData = [];
                    $imageData = [];
                    $jobOfferData = [];

                    foreach ($row as $key => $value) {
                        if (str_starts_with($key, 'jobOffer_')) {
                            $jobOfferData[str_replace('jobOffer_', '', $key)] = $value;
                        } elseif (str_starts_with($key, 'candidate_image_')) {
                            $imageData[str_replace('candidate_image_', '', $key)] = $value;
                        } elseif (str_starts_with($key, 'candidate_')) {
                            $candidateData[str_replace('candidate_', '', $key)] = $value;
                        } elseif (!str_starts_with($key, 'interview_')) {
                            $groupedResults[$appId][$key] = $value;
                        }
                    }

                    if ($hasCandidateScheme) {
                        if (!empty($imageData) && array_filter($imageData, static fn($v) => $v !== null)) {
                            $candidateData['image'] = $imageData;
                        }
                        $groupedResults[$appId]['candidate'] = array_filter($candidateData, static fn($v) => $v !== null) ? $candidateData : null;
                    }

                    if ($hasJobOfferScheme) {
                        $groupedResults[$appId]['jobOffer'] = array_filter($jobOfferData, static fn($v) => $v !== null) ? $jobOfferData : null;
                    }

                    if ($hasInterviewsScheme) {
                        $groupedResults[$appId]['interviews'] = [];
                    }
                }

                //-- Extract intervew
                if ($hasInterviewsScheme) {
                    $interviewData = [];
                    $hasInterviewValue = false;

                    foreach ($row as $key => $value) {
                        if (str_starts_with($key, 'interview_')) {
                            $realIKey = str_replace('interview_', '', $key);
                            $interviewData[$realIKey] = $value;
                            if ($value !== null) {
                                $hasInterviewValue = true;
                            }
                        }
                    }

                    // Add interviews (avoid duplicate)
                    if ($hasInterviewValue && !in_array($interviewData, $groupedResults[$appId]['interviews'], true)) {
                        $groupedResults[$appId]['interviews'][] = $interviewData;
                    }
                }
            }

            return array_values($groupedResults);
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

        // update
        $application->setStatus($newStatus);
        $this->getEntityManager()->flush();
    }





    #[Override]
    public function save(Application $application): string
    {
        $em = $this->getEntityManager();

        $candidate = $em->getReference(
            CandidateEntity::class,
            $application->getCandidateId(),
        );

        $jobOffer = $em->getReference(
            JobOfferEntity::class,
            $application->getJobOfferId(),
        );

        $company = $em->getReference(CompanyEntity::class, $application->getCompanyId());

        $candidateResume =null;
        if($application->getCandidateResumeId()){
            $candidateResume = $em->getReference(CandidateResumeEntity::class, $application->getCandidateResumeId());
        }

        $entity = ApplicationEntity::create(
            candidate: $candidate,
            jobOffer: $jobOffer,
            company: $company,
            matchScore: $application->getScore(),
            candidateResume: $candidateResume
        );

        $em->persist($entity);
        $em->flush();

        return $entity->getId();
    }


    #[Override]
    public function bulkChangeStatus(
        array $applicationIds,
        JobApplicationStatus $newStatus
    ): void {
        if ($applicationIds === []) {
            return;
        }

        $this->createQueryBuilder('a')
            ->update()
            ->set('a.status', ':status')
            ->where('a.id IN (:applicationIds)')
            ->setParameter('status', $newStatus->value)
            ->setParameter('applicationIds', $applicationIds)
            ->getQuery()
            ->execute();
    }
}