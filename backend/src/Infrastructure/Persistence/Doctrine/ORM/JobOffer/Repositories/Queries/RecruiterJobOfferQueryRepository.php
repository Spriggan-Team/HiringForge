<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Repositories\Queries;

use App\Application\Query\JobOffer\DTO\JobOfferListItem;
use App\Application\Query\JobOffer\DTO\JobOfferViewLightModel;
use App\Application\Query\JobOffer\DTO\JobSummaryItem;
use App\Application\Query\JobOffer\Repositories\RecruiterJobOfferQueryRepositoryInterface;
use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;

use App\Domain\Company\CompanyRepositoryInterface;
use App\Domain\Interviews\InterviewsRepositoryInterface;
use App\Domain\JobOffer\JobActivityStatus;
use App\Domain\JobOffer\JobPublicationStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillTranslationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferLanguageEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferSkillsEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferViewEntity;

use Doctrine\ORM\EntityManagerInterface;
use Override;



class RecruiterJobOfferQueryRepository implements RecruiterJobOfferQueryRepositoryInterface
{

    public function __construct(
        private EntityManagerInterface $em,
        private ApplicationRepositoryInterface $applicationRepository,
        private CompanyRepositoryInterface $companyRepositoryInterface,
        private InterviewsRepositoryInterface $interviewsRepository
    ){}


    //---------------------------
    //---- SKILLS
    //---------------------------

    public function getSkillIdsByJobId(string $jobId): array
    {
        if (trim($jobId) === '') {
            return [];
        }

        /** @var array<int, array{skillId: string}> $results */
        $results = $this->em->getRepository(JobOfferSkillsEntity::class)
            ->createQueryBuilder('jos')
            ->select('IDENTITY(jos.skill) AS skillId')
            ->where('jos.jobOffer = :jobId')
            ->setParameter('jobId', $jobId)
            ->getQuery()
            ->getScalarResult();

        return array_column($results, 'skillId');
    }


    //-----------------------------
    //------ STATS (Count)
    //------------------------------
    public function count(
        ?string $userId = null,
        array $criteria = []
    ): int {
        // QueryBuilder 
        $qb = $this->em->createQueryBuilder()
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


    //----------------------------------------------
    //--- FETCH: PROJECT/COLLECTION/ITEM (JOB OFFERS)
    //----------------------------------------------

    public function fetchJobOfferCardinalities(string $recruiterId, string $jobId): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('
                COUNT(DISTINCT a.id) AS candidates,
                COUNT(DISTINCT i.id) AS interviews,
                COUNT(DISTINCT jv.id) AS viewsCount,
                COUNT(DISTINCT employment.id) AS employmentOffer,
                COUNT(DISTINCT CASE WHEN a.status = :hiredStatus THEN 1 ELSE \'\' END) AS hired
            ')
            ->from(ApplicationEntity::class, 'a')
            ->innerJoin('a.jobOffer', 'j')
            ->leftJoin('j.views', 'jv')
            ->leftJoin('a.interviews', 'i')
            ->leftJoin('a.employmentOffers', 'employment')
            ->where('j.id = :jobId')
            ->andWhere('j.user = :recruiterId')
            ->setParameters([
                'recruiterId' => $recruiterId,
                'jobId'       => $jobId,
                'hiredStatus' => JobApplicationStatus::HIRED->value,
            ]);

        try {
            $result = $qb->getQuery()->getSingleResult();
        } catch (\Doctrine\ORM\NoResultException) {
            return [
                'candidatesCount'      => 0,
                'interviewsCount'      => 0,
                'employmentOfferCount' => 0,
                'hiredCount'           => 0,
                'viewsCountCount'      => 0,
            ];
        }

        return [
            'candidatesCount'      => (int) ($result['candidates'] ?? 0),
            'interviewsCount'      => (int) ($result['interviews'] ?? 0),
            'employmentOfferCount' => (int) ($result['employmentOffer'] ?? 0),
            'hiredCount'           => (int) ($result['hired'] ?? 0),
            'viewsCountCount'      => (int) ($result['viewsCount'] ?? 0),
        ];
    }


    #[Override]
    public function getJobOfferLightViewModelByCriteria(
        string $userId,
        JobActivityStatus $activityStatus,
        int $skip = 0,
        int $limit = 5
    ): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select(
                'j.id, j.title, j.jobWorkMode, j.createdAt AS jobCreationDate',
                'f.name AS imageName',
                'addr.city',
                'c.label AS contractType',
                'COUNT(DISTINCT i.id) AS interviewCount',
                'COUNT(DISTINCT a.id) AS totalAppCount',
                'SUM(CASE WHEN a.status NOT IN (:treatedAppStatus) THEN 1 ELSE 0 END) AS remainingApp'
            )
            ->from(JobOfferEntity::class, 'j')
            ->leftJoin('j.address', 'addr')
            ->leftJoin('j.images', 'jf')
            ->leftJoin('jf.file', 'f')
            ->leftJoin('j.contractType', 'c')
            ->innerJoin('j.applications', 'a')
            ->innerJoin('a.interviews', 'i')
            ->where('j.user = :user')
            ->andWhere('j.activityStatus = :activityStatus')
            ->groupBy('j.id, f.name, addr.city, c.label')
            ->setParameters([
                'user' => $userId,
                'activityStatus' => $activityStatus, 
                'treatedAppStatus' => [
                        JobApplicationStatus::REJECTED, 
                        JobApplicationStatus::HIRED,
                        JobApplicationStatus::WITHDRAWN
                    ]
            ])
            ->setFirstResult($skip)
            ->setMaxResults($limit);

        $results = $qb->getQuery()
                      ->getArrayResult();
        
        // ApiResponse::$logger->error("SQL DUM: ". $qb->getQuery()->getSQL());
        // dd( $qb->getQuery()->getSQL());
        // dump( $qb->getQuery()->getSQL());

        $response = [];
        $now = new \DateTimeImmutable();

        foreach ($results as $value) {
            $delay = $value['jobCreationDate'] instanceof \DateTimeInterface
                ? $now->getTimestamp() - $value['jobCreationDate']->getTimestamp()
                : 0;

            $totalAppCount = (int) ($value['totalAppCount'] ?? 0);
            $remainingApp = (int) ($value['remainingApp'] ?? 0);

            $treatmentProgress = $totalAppCount > 0 
                ? ($remainingApp / $totalAppCount) 
                : 0;

            $response[] = new JobOfferViewLightModel(
                id: $value['id'],
                title: $value['title'],
                image: $value['imageName'],
                interviews: (int) $value['interviewCount'] ?? 0,
                tags: array_values(array_filter([
                    $value['contractType'],
                    $value['jobWorkMode'],
                    $value['city']
                ], fn($item) => $item !== null)),
                delay: $delay,
                treatmentProgress: $treatmentProgress,
                remainingCandidates: $remainingApp,
                candidates: $remainingApp
            );
        }

        return $response;
    }


    public function fetchJobOfferViewById(
        string $offerId,
        ?string $userId = null,
    ): JobOfferListItem
    {
        
        throw new \Exception('Not implemented');
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

        $qb = $this->em->createQueryBuilder()
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
        if (!empty($scheme['mainImage']) || !empty($scheme['mainImageFileId'])) {
            $qb->leftJoin('j.images', 'ji', 'WITH', 'ji.isMain = true')
               ->leftJoin('ji.file', 'jf');

            $selects[] = 'jf.id AS mainImageFileId';
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
        if (!empty($scheme['mainImageFileId'])) {
            $output['mainImageFileId'] = array_key_exists('mainImageFileId', $baseData) 
                ? $baseData['mainImageFileId'] 
                : null;
        }

        if (!empty($scheme['mainImage'])) {
            $output['mainImage'] = array_key_exists('mainImage', $baseData) 
                ? $baseData['mainImage'] 
                : null;
        }


        // Handle 1-N collections (Languages, Skills) & Views Count

        // Views count
        if (!empty($scheme['viewsCount'])) {
            $viewsCount = (int) $this->em->createQueryBuilder()
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
            $langQb = $this->em->createQueryBuilder()
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

            $skillQb = $this->em->createQueryBuilder()
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


    /**
     * @return array<int,JobSummaryItem>
    */
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
        }
        catch (\Throwable $e) {
            $companyId = null;
        }

        // Main QueryBuilder 
        $qb = $this->em->createQueryBuilder()
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

        // Applying Strict Condition Filters (Filter Flexibility)
        if (!empty($pubStatuses) || !empty($actStatuses)) {
            if (!empty($pubStatuses)) {
                $qb->andWhere('j.publicationStatus IN (:pubStatuses)')
                   ->setParameter('pubStatuses', $pubStatuses);
            }

            if (!empty($actStatuses)) {
                $qb->andWhere('j.activityStatus IN (:actStatuses)')
                    ->setParameter('actStatuses', $actStatuses);
            }
        }

        // Salary Filters
        if (isset($criteria['salary']) && is_numeric($criteria['salary']) && (float)$criteria['salary'] > 0) {
            $qb->andWhere('j.minSalary >= :minSalary')
               ->setParameter('minSalary', (float) $criteria['salary']);
        }

        // Application Count Filters
        if (isset($criteria['candidateCount']) && is_numeric($criteria['candidateCount'])) {
            $minCandidates = (int) $criteria['candidateCount'];

            if ($minCandidates > 0) {
                $subQuery = $this->em->createQueryBuilder()
                    ->select('COUNT(app.id)')
                    ->from(ApplicationEntity::class, 'app')
                    ->where('app.jobOffer = j.id');

                $qb->andWhere('(' . $subQuery->getDQL() . ') >= :minCandidates')
                   ->setParameter('minCandidates', $minCandidates);
            }
        }

        // Text Search Filter (Title)
        if (!empty($criteria['searchText'])) {
            $qb->andWhere('LOWER(j.title) LIKE :searchText')
               ->setParameter('searchText', '%' . mb_strtolower(trim($criteria['searchText'])) . '%');
        }
        $qb->orderBy('j.createdAt', 'DESC');

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

            $candidatesCount = $companyId ? (int) $this->applicationRepository->countApplications(['companyId' => $companyId, 'jobOfferId' => $jobId]) : 0;
            $interviewsCount = $companyId ? (int) $this->interviewsRepository->countInterviews(['companyId' => $companyId, 'jobOfferId' => $jobId]) : 0;
            $hiredCount      = $companyId ? (int) $this->applicationRepository->countApplications(['companyId' => $companyId, 'jobOfferId' => $jobId, 'status' => JobApplicationStatus::HIRED->value]) : 0;

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
                    'employmentOffers'     => 0,
                    'hired'      => $hiredCount,
                ]
            );
        }, $results);
    }


}