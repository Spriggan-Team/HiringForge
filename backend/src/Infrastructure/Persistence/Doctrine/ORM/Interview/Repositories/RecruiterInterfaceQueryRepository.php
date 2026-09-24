<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Interview\Repositories;


use App\Application\Query\Interviews\Repositories\RecruiterInterviewsQueryRepositoryInterface;
use App\Domain\Interviews\InterviewStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\Repositories\Mapper\InterviewEntityMapper;


use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Override;



class RecruiterInterfaceQueryRepository extends ServiceEntityRepository
    implements RecruiterInterviewsQueryRepositoryInterface
{

    public function __construct(
        ManagerRegistry $registry,
        private InterviewEntityMapper $mapper
    )
    {
        parent::__construct($registry, InterviewEntity::class);
    }

    

    
    #[Override]
    public function getCalendarCollectionViews(
        string $userId,
        \DateTimeImmutable $month
    ): array {
        $startOfMonth = $month->modify('first day of this month')
                              ->setTime(0, 0, 0);

        $startOfNextMonth = $startOfMonth->modify('+1 month');

        $interviews = $this->createQueryBuilder('i')
            ->select(
                'i.id AS id',
                'i.startDate AS startDate',
                'i.title AS title',
                'i.type AS type',
                'i.status AS status',
                'i.minutes AS minutes'
            )
            ->innerJoin('i.application', 'a')
            ->innerJoin('a.jobOffer', 'j')
            ->where('j.user = :userId')
            ->andWhere('i.startDate >= :startOfMonth')
            ->andWhere('i.startDate < :startOfNextMonth')
            ->setParameter('userId', $userId)
            ->setParameter('startOfMonth', $startOfMonth)
            ->setParameter('startOfNextMonth', $startOfNextMonth)
            ->orderBy('i.startDate', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $groupedInterviews = [];

        foreach ($interviews as $interview) {
            $date = $interview['startDate'];
            //-- Treat Datetime
            if ($date instanceof \DateTimeInterface) {
                $dayKey = $date->format('Y-m-d');
                $interview['startDate'] = $date->format(
                    \DateTimeInterface::ATOM
                );
            }
            else {
                $dayKey = (new \DateTimeImmutable($date))->format('Y-m-d');
            }
            $groupedInterviews[$dayKey][] = $interview;
        }

        return $groupedInterviews;
    }



    /**
     * @param string $userId - refers to recruiter's id
     * @return array<int, array{
     *      id: string,
     *      type: string,
     *      title: string,
     *      url?: string,
     *      candidate: array{
     *          id: string,
     *          firstName: string,
     *          email: string,
     *          lastName: string,
     *          imageId?: int
     *      },
     *      description?: string,
     *      startDate: \DateTimeImmutable,
     *      rejectionReason?: string,
     *      minutes: int
     * }>
     */
    #[Override]
    public function getInterviewAgenda(
        string $userId,
        int $skip,
        int $limit,
        \DateTimeImmutable $date
    ): array {
        $dayStart = $date->setTime(0, 0, 0);
        $dayEnd =  $dayStart->modify('+1 day');

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
            ->orderBy(
                '
                    CASE
                        WHEN i.status = :scheduled
                            AND i.candidateApproval = false
                        THEN 0

                        WHEN i.status = :progress THEN 1
                        WHEN i.candidateApproval = true THEN 2
                        WHEN i.status = :completed THEN 3
                        WHEN i.candidateApproval = false  THEN 4
                        WHEN i.status = :close  THEN 5
                        WHEN i.status = :missed THEN 6
                        
                        ELSE 7
                    END
                ',
                'ASC'
            )
            ->addOrderBy('i.startDate', 'ASC')
            ->setParameter('scheduled', InterviewStatus::SCHEDULED->value)
            ->setParameter('completed', InterviewStatus::COMPLETED->value)
            ->setParameter('close', InterviewStatus::CLOSED->value)
            ->setParameter('missed', InterviewStatus::MISSED->value)
            ->setParameter('progress', InterviewStatus::IN_PROGRESS->value)
            ->getQuery()
            ->getResult();


        return array_map(static function (InterviewEntity $interview): array {
            $application = $interview->getApplication();
            $candidate = $application->getCandidate();

            $data = [
                'id' => $interview->getId(),
                'title' =>$interview->getTitle(),
                'type' => $interview->getType(),
                'url' => $interview->getURL(),
                'candidate' => [
                    'id' => $candidate->getId(),
                    'firstName' => $candidate->getFirstName(),
                    'lastName' => $candidate->getLastName(),
                    'email' => $candidate->getEmail(),
                ],
                'startDate' => $interview->getStartDate()->format(\DateTimeInterface::ATOM),
                'minutes' => $interview->getMinutes(),
                'rejectionReason'=> $interview->getRejectionReason()
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


    /**
     * ------------------
     * Projection
     * ------------------
     */
    
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
     *      description?: bool,
     *      status?: bool,
     *      url?: bool,
     *      job?: array{
     *          title?: bool   
     *      },
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
                   ->innerJoin('i.application', 'a')
                   ->innerJoin('a.jobOffer', 'jo');
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
                    $selectedFields[] = "c.$field AS candidate_$field";
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
        // Job Offer projection
        //--------------------------------

        if (
            !empty($scheme['job'])
            && is_array($scheme['job'])
        ) {
            $allowedJobOfferFields = [
                'id',
                'title',
            ];
            
            foreach ($allowedJobOfferFields as $field) {
                if (!empty($scheme['job'][$field])) {
                    $selectedFields[] = "jo.$field AS job_$field";
                }
            }
        }


        //--------------------------------
        // Default projection
        //--------------------------------

        if (empty($selectedFields)) {
            $selectedFields[] = 'i.id';
            $selectedFields[] = "jo.id AS job_id";
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
            $qb->andWhere('jo.user = :userId')
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

        $results = array_map(
            static function (array $row): array {
                if (
                    isset($row['startDate'])
                    && $row['startDate'] instanceof \DateTimeInterface
                ) {
                    $row['startDate'] = $row['startDate']->format(
                        \DateTimeInterface::ATOM
                    );
                }

                return $row;
            },
            $results
        );

        if (!empty($scheme['candidate']) || !empty($scheme['job'])) {
            return array_map(
                static function (array $row) use($scheme) : array {
                    //-- Preparing embedding object

                    $candidateData = [];
                    $imageData = [];
                    $jobData = [];
                    
                    foreach ($row as $key => $value) {
                        //-- Job handling
                        if (
                            !empty($scheme['job'])
                            && str_starts_with($key, 'job_')
                        ) {
                            $realKey = substr($key, strlen('job_'));

                            $jobData[$realKey] = $value;
                            unset($row[$key]);

                            continue;
                        }

                        //-- Candidate Handling
                        if(!empty($scheme['candidate'])){
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
                    }

                    //-- Merge structured data

                    if (!empty($jobData)) {
                        $row['job'] = $jobData;
                    }

                    if(!empty($scheme['candidate'])){
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
                    }

                    return $row;
                },
                $results
            );
        }

        return $results;
    }

}