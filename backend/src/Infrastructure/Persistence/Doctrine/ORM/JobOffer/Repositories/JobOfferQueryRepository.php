<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Repositories;

use App\Application\Query\JobOffer\DTO\JobOfferStatistics;

use App\Application\Query\JobOffer\DTO\JobOfferListItem;
use App\Application\Query\JobOffer\DTO\JobSummaryItem;
use App\Application\Query\JobOffer\JobOfferQueryRepositoryInterace;
use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;

use App\Domain\JobOffer\JobActivityStatus;
use App\Domain\JobOffer\JobPublicationStatus;
use App\Domain\Company\CompanyRepositoryInterface;
use App\Domain\Interviews\InterviewsRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillTranslationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferLanguageEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferSkillsEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferViewEntity;
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
        private ApplicationRepositoryInterface $applicationRepository,
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
            $qbStats->innerJoin("a.jobOfffer", 'j')
                    ->where("j.user = :userId")
                    ->setParameter("userId", $userId);
        }

        $stats = $qbStats->getQuery()->getSingleResult();

        //  Breakdown of Interviews Related to the JobOffer
        $qbInterviews =  $this->manager
            ->createQueryBuilder()
            ->select('COUNT(i.id)')
            ->from(InterviewEntity::class, 'i');
        
        if($isJobIdProvided){
            $qbInterviews->where("i.jobOffer = :jobId")
                            ->setParameter("jobId", $jobId);
        }
        else{
            $qbInterviews->innerJoin('i.jobOffer', 'j')
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
    /**
     * Dynamically retrieve a projection of an offer based on scheme
     * @param string $jobOfferId Target job offer ID
     * @param string $userId Owner/recruiter user ID
     * @param array $scheme Schema mask defining which fields to select
     * @return array
     */
    public function fetchJobOfferProjection(string $jobOfferId, string $userId, array $scheme = []): array
    {
        // Scheme filtering and cleaning
        $scheme = array_filter($scheme, fn($v) => !empty($v));

        $qb = $this->manager->createQueryBuilder()
            ->from(JobOfferEntity::class, 'j')
            ->where('j.id = :jobOfferId')
            ->andWhere('j.user = :userId')
            ->setParameter('jobOfferId', $jobOfferId)
            ->setParameter('userId', $userId);

        // 1. Base Selection (Direct Fields)
        $selects = [];
        
        $directFields = [
            'id'                => 'j.id',
            'title'             => 'j.title',
            'content'           => 'j.content',
            'jobWorkMode'       => 'j.jobWorkMode',
            'publicationStatus' => 'j.publicationStatus',
            'activityStatus'    => 'j.activityStatus',
            'visibilityStatus'  => 'j.visibilityStatus',
            'createdAt'         => 'j.createdAt',
            'updatedAt'         => 'j.updatedAt',
        ];

        foreach ($directFields as $key => $dqlField) {
            if (!empty($scheme[$key])) {
                $selects[] = "$dqlField AS $key";
            }
        }

        // Salary
        if (!empty($scheme['salary'])) {
            $salaryScheme = (array) $scheme['salary'];
            if (!empty($salaryScheme['devise'])) $selects[] = 'j.currency AS salary_devise';
            if (!empty($salaryScheme['min']))    $selects[] = 'j.minSalary AS salary_min';
            if (!empty($salaryScheme['max']))    $selects[] = 'j.maxSalary AS salary_max';
        }

        // Location / Address
        if (!empty($scheme['location'])) {
            $qb->leftJoin('j.address', 'ja');
            $locScheme = (array) $scheme['location'];
            if (!empty($locScheme['id']))      $selects[] = 'ja.id AS location_id';
            if (!empty($locScheme['street']))  $selects[] = 'ja.street AS location_street';
            if (!empty($locScheme['city']))    $selects[] = 'ja.city AS location_city';
            if (!empty($locScheme['country'])) $selects[] = 'ja.country AS location_country';
        }

        // Department
        if (!empty($scheme['department'])) {
            $qb->leftJoin('j.department', 'jd');
            $deptScheme = (array) $scheme['department'];
            if (!empty($deptScheme['id']))    $selects[] = 'jd.id AS department_id';
            if (!empty($deptScheme['label'])) $selects[] = 'jd.label AS department_label';
        }

        // Contract
        if (!empty($scheme['contract'])) {
            $qb->leftJoin('j.contractType', 'jc');
            $contractScheme = (array) $scheme['contract'];
            if (!empty($contractScheme['id']))    $selects[] = 'jc.id AS contract_id';
            if (!empty($contractScheme['label'])) $selects[] = 'jc.label AS contract_label';
        }

        // Main Image
        if (!empty($scheme['mainImage'])) {
            $qb->leftJoin('j.images', 'ji', 'WITH', 'ji.isMain = true')
               ->leftJoin('ji.file', 'jf');

            $selects[] = 'jf.name AS mainImage'; 
        }

        // Fallback: Default to selecting ID if no direct field is specified
        if (empty($selects)) {
            $selects[] = 'j.id AS id';
        }

        $qb->select(implode(', ', $selects));
        $baseData = $qb->getQuery()->getOneOrNullResult();

        if (!$baseData) {
            return [];
        }

        // Helper to format BackedEnum instances or scalar values
        $formatEnum = static function (mixed $value): mixed {
            return $value instanceof \BackedEnum ? $value->value : $value;
        };

        // 2. Output Payload Construction
        $output = [];

        // Format direct scalar fields
        foreach (['id', 'title', 'content'] as $f) {
            if (!empty($scheme[$f])) $output[$f] = $baseData[$f] ?? null;
        }

        // Format enum fields
        foreach (['jobWorkMode', 'publicationStatus', 'activityStatus', 'visibilityStatus'] as $enumField) {
            if (!empty($scheme[$enumField])) {
                $output[$enumField] = isset($baseData[$enumField]) 
                    ? $formatEnum($baseData[$enumField]) 
                    : null;
            }
        }

        // Format timestamps
        if (!empty($scheme['createdAt'])) {
            $output['createdAt'] = $baseData['createdAt'] instanceof \DateTimeInterface 
                ? $baseData['createdAt']->format(\DateTimeInterface::ATOM) 
                : $baseData['createdAt'];
        }
        if (!empty($scheme['updatedAt'])) {
            $output['updatedAt'] = $baseData['updatedAt'] instanceof \DateTimeInterface 
                ? $baseData['updatedAt']->format(\DateTimeInterface::ATOM) 
                : $baseData['updatedAt'];
        }

        // Salary Object
        if (!empty($scheme['salary'])) {
            $output['salary'] = array_filter([
                'devise' => $baseData['salary_devise'] ?? null,
                'min'    => isset($baseData['salary_min']) ? (float) $baseData['salary_min'] : null,
                'max'    => isset($baseData['salary_max']) ? (float) $baseData['salary_max'] : null,
            ], fn($v) => $v !== null);
        }

        // Location Object
        if (!empty($scheme['location'])) {
            $output['location'] = array_filter([
                'id'      => $baseData['location_id'] ?? null,
                'street'  => $baseData['location_street'] ?? null,
                'city'    => $baseData['location_city'] ?? null,
                'country' => $baseData['location_country'] ?? null,
            ], fn($v) => $v !== null);
        }

        // Department Object
        if (!empty($scheme['department'])) {
            $output['department'] = array_filter([
                'id'    => $baseData['department_id'] ?? null,
                'label' => $baseData['department_label'] ?? null,
            ], fn($v) => $v !== null);
        }

        // Contract Object
        if (!empty($scheme['contract'])) {
            $output['contract'] = array_filter([
                'id'    => $baseData['contract_id'] ?? null,
                'label' => $baseData['contract_label'] ?? null,
            ], fn($v) => $v !== null);
        }

        // Main Image
        if (!empty($scheme['mainImage'])) {
            $output['mainImage'] = $baseData['mainImage'] ?? null;
        }

        // Handle 1-N collections (Languages, Skills) & Views Count

        // Views count
        if (!empty($scheme['viewsCount'])) {
            $viewsCount = (int) $this->manager->createQueryBuilder()
                ->select('COUNT(v.id)')
                ->from(JobOfferViewEntity::class, 'v')
                ->where('v.jobOffer = :jobOfferId')
                ->setParameter('jobOfferId', $jobOfferId)
                ->getQuery()
                ->getSingleScalarResult();

            $output['viewsCount'] = $viewsCount;
        }

        // Languages (Array of Objects)
        if (!empty($scheme['languages'])) {
            $langScheme = (array) $scheme['languages'];
            $langQb = $this->manager->createQueryBuilder()
                ->from(JobOfferLanguageEntity::class, 'jol')
                ->leftJoin('jol.language', 'l')
                ->where('jol.jobOffer = :jobOfferId')
                ->setParameter('jobOfferId', $jobOfferId);

            $langSelects = [];
            if (!empty($langScheme['label'])) $langSelects[] = 'l.label AS label';
            if (!empty($langScheme['level'])) $langSelects[] = 'jol.level AS level';

            if (!empty($langSelects)) {
                $langQb->select(implode(', ', $langSelects));
                $rawLangs = $langQb->getQuery()->getArrayResult();

                $output['languages'] = array_map(function ($row) use ($formatEnum) {
                    if (isset($row['level'])) {
                        $row['level'] = $formatEnum($row['level']);
                    }
                    return $row;
                }, $rawLangs);
            } else {
                $output['languages'] = [];
            }
        }

        // Skills (Array of objects with translation/locale support)
        if (!empty($scheme['skills'])) {
            $skillScheme = (array) $scheme['skills'];

            // Retrieve requested locale (defaults to 'en')
            $locale = !empty($skillScheme['locale']) ? (string) $skillScheme['locale'] : 'en';

            $skillQb = $this->manager->createQueryBuilder()
                ->from(JobOfferSkillsEntity::class, 'jos')
                ->innerJoin('jos.skill', 'sk')
                ->leftJoin(
                    SkillTranslationEntity::class, 
                    'st', 
                    'WITH', 
                    'st.skill = sk.id AND st.language = :locale'
                )
                ->where('jos.jobOffer = :jobOfferId')
                ->setParameter('jobOfferId', $jobOfferId)
                ->setParameter('locale', $locale);

            $skillSelects = [];

            if (!empty($skillScheme['id'])) {
                $skillSelects[] = 'sk.id AS id';
            }

            // Translated Name (Falls back to canonicalName if missing)
            if (!empty($skillScheme['name']) || !empty($skillScheme['canonicalName'])) {
                $skillSelects[] = 'COALESCE(st.name, sk.canonicalName) AS name';
            }

            if (isset($skillScheme['isRequired'])) {
                $skillSelects[] = 'jos.isRequired AS isRequired';
            }

            // Fallback: If 'skills' => true was passed without child fields
            if (empty($skillSelects)) {
                $skillSelects = [
                    'sk.id AS id', 
                    'COALESCE(st.name, sk.canonicalName) AS name', 
                    'jos.isRequired AS isRequired'
                ];
            }

            $skillQb->select(implode(', ', $skillSelects));
            $rawSkills = $skillQb->getQuery()->getArrayResult();

            $output['skills'] = array_map(function (array $skill) {
                if (array_key_exists('isRequired', $skill)) {
                    $skill['isRequired'] = (bool) $skill['isRequired'];
                }
                return $skill;
            }, $rawSkills);
        }

        return $output;
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