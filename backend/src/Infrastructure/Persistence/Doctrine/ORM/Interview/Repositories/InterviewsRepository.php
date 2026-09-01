<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Interview\Repositories;

use App\Domain\Interviews\Interview;
use App\Domain\Interviews\InterviewContext;
use App\Domain\Interviews\InterviewsRepositoryInterface;
use App\Domain\Interviews\InterviewStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\Repositories\Mapper\InterviewEntityMapper;
use DateTimeImmutable;
use Override;

use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Symfony\Component\Finder\Exception\AccessDeniedException;



class InterviewsRepository extends ServiceEntityRepository 
    implements InterviewsRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private InterviewEntityMapper $mapper
    )
    {
        parent::__construct($registry, InterviewEntity::class);
    }

    
    #[Override]
    public function getInterviewContext(string $interviewId): ?InterviewContext
    {
        $data = $this->createQueryBuilder('i')
            ->select(
                'i.id AS interviewId',
                'j.id AS jobId',
                'j.title AS jobTitle',
                'IDENTITY(a.candidate) AS candidateId'
            )
            ->innerJoin('i.application', 'a')
            ->innerJoin('a.jobOffer', 'j')
            ->where('i.id = :interviewId')
            ->setParameter('interviewId', $interviewId)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$data) {
            return null;
        }

        return new InterviewContext(
            jobId: $data['jobId'],
            jobTitle: $data['jobTitle'],
            interviewId: $data['interviewId'],
            candidateId: $data['candidateId']
        );
    }


    #[Override]
    public function isUserAssociatedWithInterview(
        string $userId,
        string $interviewId
    ): bool {
        $count = $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(i.id)')
            ->from(ApplicationEntity::class, 'a')
            ->innerJoin('a.interviews', 'i')
            ->innerJoin('a.jobOffer', 'j')
            ->where('j.user = :userId')
            ->andWhere('i.id = :interviewId')
            ->setParameter('userId', $userId)
            ->setParameter('interviewId', $interviewId)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }

    
    #[Override]
    public function save(Interview $interview): void
    {
        $entity = null;

        if ($interview->getId() !== null) {
            $entity = $this->em->find(
                InterviewEntity::class,
                $interview->getId()
            );
        }

        $entity = $this->mapper->toEntity(
            interview: $interview,
            entity: $entity
        );

        $em = $this->getEntityManager();
        $em->persist($entity);
        $em->flush();
    }


    #[Override]
    public function remove(string $interviewId): void
    {
        $entity = $this->find($interviewId);

        if ($entity === null) {
            throw new \DomainException(
                sprintf('Interview "%s" not found.', $interviewId)
            );
        }

        $em = $this->getEntityManager();
        $em->remove($entity);
        $em->flush();
    }


    #[Override]
    public function isConfirmedByCandidate(string $interviewId): bool
    {
        $result = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.id = :interviewId')
            ->andWhere('i.candidateApproval = :approved')
            ->setParameter('interviewId', $interviewId)
            ->setParameter('approved', true)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result > 0;
    }


    #[Override]
    public function findConcurrentInterviews(DateTimeImmutable $startDate, int $minutes): bool
    {
        $endDate = $startDate->modify(sprintf('+%d minutes', $minutes));

        $count = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.startDate < :endDate')
            ->andWhere('DATE_ADD(i.startDate, i.minutes, \'MINUTE\') > :startDate')
            ->andWhere('i.status != :cancelledStatus')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('cancelledStatus', InterviewStatus::CLOSED)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }


    #[Override]
    public function assertRecruiterHasAccessToInterview(
        string $recruiterId,
        string $candidateId,
        string $interviewId
    ): void {
        $exists = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('1')
            ->from(ApplicationEntity::class, 'a')
            ->innerJoin('a.candidate', 'c')
            ->innerJoin('a.interviews', 'i')
            ->innerJoin('a.jobOffer', 'j')
            ->where('IDENTITY(j.user) = :recruiterId')
            ->andWhere('c.id = :candidateId')
            ->andWhere('i.id = :interviewId')
            ->setParameter('recruiterId', $recruiterId)
            ->setParameter('candidateId', $candidateId)
            ->setParameter('interviewId', $interviewId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($exists === null) {
            throw new AccessDeniedException(
                'You do not have access to this interview.'
            );
        }
    }


    /**
     * @param string $userId - refers to recruiter's id
     * @return array<int, array{
     *      id: string,
     *      type: string,
     *      candidate: array{
     *          id: string,
     *          firstName: string,
     *          email: string,
     *          lastName: string,
     *          imageId?: int
     *      },
     *      description?: string,
     *      startDate: \DateTimeImmutable,
     *      minutes: int
     * }>
     */
    public function getTodayInterviewAgenda(
        string $userId,
        int $skip,
        int $limit,
        \DateTimeImmutable $date
    ): array {
        $dayStart = $date->setTime(0, 0, 0);
        $dayEnd = $dayStart->modify('+1 day');

        $interviews = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('i', 'a', 'c')
            ->from(InterviewEntity::class, 'i')
            ->innerJoin('i.application', 'a')
            ->innerJoin('a.candidate', 'c')
            ->innerJoin('a.jobOffer', 'j')
            ->where('j.user = :userId')
            ->andWhere('i.startDate >= :dayStart')
            ->andWhere('i.startDate < :dayEnd')
            ->orderBy('i.startDate', 'ASC')
            ->setFirstResult($skip)
            ->setMaxResults($limit)
            ->setParameters([
                'userId' => $userId,
                'dayStart' => $dayStart,
                'dayEnd' => $dayEnd,
            ])
            ->getQuery()
            ->getResult();

        return array_map(static function (InterviewEntity $interview): array {
            $application = $interview->getApplication();
            $candidate = $application->getCandidate();

            $data = [
                'id' => $interview->getId(),
                'type' => $interview->getType(),
                'candidate' => [
                    'id' => $candidate->getId(),
                    'firstName' => $candidate->getFirstName(),
                    'lastName' => $candidate->getLastName(),
                    'email' => $candidate->getEmail(),
                ],
                'startDate' => $interview->getStartDate()->format(\DateTimeInterface::ATOM),
                'minutes' => $interview->getMinutes(),
            ];

            if ($interview->getDescription() !== null) {
                $data['description'] = $interview->getDescription();
            }

            if ($candidate->getImage()->getId() !== null) {
                $data['candidate']['imageId'] = $candidate->getImage()->getId();
            }

            return $data;
        }, $interviews);
    }

    


    #[Override]
    public function cancelInterviewPlansForApplication(string $recruiterId, string $applicationId): void
    {
        $this->createQueryBuilder('i')
            ->update()
            ->set('i.status', ':status')
            ->where('i.application = :applicationId')
            ->andWhere('i.user = :recruiterId')
            ->setParameter('status', InterviewStatus::CLOSED->value)
            ->setParameter('applicationId', $applicationId)
            ->setParameter('recruiterId', $recruiterId)
            ->getQuery()
            ->execute();
    }
    

    /**
     * Retrieve interviews related to a specific job linked to a recruiter (user),
     * a company, or a specific candidate.
     *
     * @param string $jobId
     * @param int $limit
     * @param int $skip
     * @param array{
     *      id?: bool,
     *      startDate?: bool,
     *      minutes?: bool,
     *      title?: bool,
     * 
     *      description?: bool,
     *      status?: bool,
     *      url?: bool,
     *      candidate?: array{
     *          id?: bool,
     *          firstName?: bool,
     *          lastName?: bool,
     *          email?: bool,
     *          image?: bool|array{
     *              id?: bool,
     *              name?: bool,
     *              size?: bool,
     *              mime?: bool
     *          }
     *      }
     * } $scheme
     * @param string|null $userId
     * @param string|null $companyId
     * @param string|null $candidateId
     * @return array
     */
    #[Override]
    public function fetchJobInterviewsProjection(
        string $userId,
        int $skip = 0,
        int $limit = 17,
        ?string $jobId = null,
        array $scheme = ['id' => true],
        ?string $companyId = null,
        ?string $candidateId = null,
        ?array $statuses= null,
    ): array {
        $qb = $this->createQueryBuilder('i')
                   ->innerJoin('i.application', 'a');
        $selectedFields = [];

        //--------------------------------
        // Interview projection
        //--------------------------------

        $allowedInterviewFields = [
            'id',
            'title',
            'startDate',
            'minutes',
            'description',
            'status',
            'url',
        ];

        foreach ($allowedInterviewFields as $field) {
            if (!empty($scheme[$field])) {
                $selectedFields[] = "i.$field";
            }
        }

        //--------------------------------
        // Candidate projection
        //--------------------------------

        if (
            !empty($scheme['candidate'])
            && is_array($scheme['candidate'])
        ) {
            $qb->leftJoin('a.candidate', 'c');

            $allowedCandidateFields = [
                'id',
                'firstName',
                'lastName',
                'email',
            ];

            foreach ($allowedCandidateFields as $field) {
                if (!empty($scheme['candidate'][$field])) {
                    $selectedFields[] =
                        "c.$field AS candidate_$field";
                }
            }

            //--------------------------------
            // Candidate image projection
            //--------------------------------

            if (!empty($scheme['candidate']['image'])) {
                $qb->leftJoin('c.image', 'img');

                $imageScheme = $scheme['candidate']['image'];

                if ($imageScheme === true) {
                    $selectedFields[] = 'img.name AS candidate_image_name';
                }
                elseif (is_array($imageScheme)) {
                    $allowedImageFields = [
                        'id',
                        'name',
                        'size',
                        'mime',
                    ];

                    foreach ($allowedImageFields as $field) {
                        if (!empty($imageScheme[$field])) {
                            $selectedFields[] =
                                "img.$field AS candidate_image_$field";
                        }
                    }
                }
            }
        }

        //--------------------------------
        // Default projection
        //--------------------------------

        if (empty($selectedFields)) {
            $selectedFields[] = 'i.id';
        }

        $qb->select(implode(', ', $selectedFields));


        //--------------------------------
        // Filters
        //--------------------------------

        if ($jobId) {
            $qb->andWhere('a.jobOffer = :jobId')
                ->setParameter('jobId', $jobId);
        }

        if (!empty($statuses)) {
            $qb->andWhere('i.status IN (:statuses)')
                ->setParameter('statuses', $statuses);
        }

        //--------------------------------
        // Context filter
        //--------------------------------

        if ($candidateId) {
            $qb->andWhere('a.candidate = :candidateId')
                ->setParameter('candidateId', $candidateId);
        }
        elseif ($companyId) {
            $qb->andWhere('a.company = :companyId')
                ->setParameter('companyId', $companyId);
        }
        elseif ($userId) {
            $qb->innerJoin('a.jobOffer', 'jo')
                ->andWhere('jo.user = :userId')
                ->setParameter('userId', $userId);
        }

        //--------------------------------
        // Pagination
        //--------------------------------

        $qb
            ->setFirstResult(max(0, $skip))
            ->setMaxResults(max(1, $limit));

        $results = $qb
            ->getQuery()
            ->getArrayResult();

        //--------------------------------
        // Restructure candidate tree
        //--------------------------------

        if (!empty($scheme['candidate'])) {
            return array_map(
                static function (array $row): array {
                    $candidateData = [];
                    $imageData = [];
                    foreach ($row as $key => $value) {
                        if (str_starts_with($key, 'candidate_image_')) {
                            $realKey = str_replace(
                                'candidate_image_',
                                '',
                                $key
                            );

                            $imageData[$realKey] = $value;
                            unset($row[$key]);
                            continue;
                        }

                        if (str_starts_with($key, 'candidate_')) {
                            $realKey = str_replace(
                                'candidate_',
                                '',
                                $key
                            );
                            $candidateData[$realKey] = $value;
                            unset($row[$key]);
                        }
                    }

                    if (
                        !empty($imageData)
                        && array_filter(
                            $imageData,
                            static fn ($value) => $value !== null
                        )
                    ) {
                        $candidateData['image'] = $imageData;
                    }

                    if (!empty($candidateData)) {
                        $row['candidate'] = $candidateData;
                    }
                    return $row;
                },
                $results
            );
        }

        return $results;
    }


    #[Override]
    public function findById(string $id): ?Interview
    {
        /** @var InterviewEntity|null $entity */
        $entity = $this->find($id);

        if ($entity === null) {
            return null;
        }
        return $this->mapper->toDomain($entity);
    }




    public function countInterviews(array $criteria): int 
    {
        $qb = $this->createQueryBuilder('i')
                   ->select('COUNT(DISTINCT i.id)');

        //-- Filter by status
        if (!empty($criteria['status'])) {
            $qb->andWhere('i.status = :status')
               ->setParameter('status', $criteria['status']);
        }

        if(!empty($criteria['jobOfferId']) || !empty($criteria['companyId']))
        {
            $qb->innerJoin('i.application', 'a');

            // Direct filter : check if an iterviews posses a relation with the procided offer
            if (!empty($criteria['jobOfferId'])) {
                $qb->andWhere('a.jobOffer = :jobOfferId')
                   ->setParameter('jobOfferId', $criteria['jobOfferId']);
            }

            // Filter By Compoany par Company (companyId)
            if (!empty($criteria['companyId'])) {
                $qb->innerJoin('a.jobOffer', 'j');
                $qb->andWhere('j.company = :companyId')
                   ->setParameter('companyId', $criteria['companyId']);
            }  
        }

        try {
            return (int) $qb->getQuery()->getSingleScalarResult();
        }
        catch (NoResultException | NonUniqueResultException) {
            return 0;
        }
    }
}