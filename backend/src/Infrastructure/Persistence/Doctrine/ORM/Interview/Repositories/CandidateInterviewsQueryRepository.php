<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Interview\Repositories;


use App\Domain\Interviews\InterviewStatus;
use App\Domain\Interviews\InterviewType;
use App\Application\Query\Interviews\Repositories\CandidateInterviewsQueryRepositoryInterface;

use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\Repositories\Mapper\InterviewEntityMapper;


use Override;
use DateTimeImmutable;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


class CandidateInterviewsQueryRepository extends ServiceEntityRepository
    implements CandidateInterviewsQueryRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private InterviewEntityMapper $mapper
    )
    {
        parent::__construct($registry, InterviewEntity::class);
    }


    /**
     * @param string $candidateId - refers to candidate's id
     * @param int $skip - amount of result to ignore in a row
     * @param int $limit - max amount of result to retreive
     * @param \DateTimeImmutable $date - specific day within the quuery shoulb be exetuted
     * @param array<int, InterviewStatus> $statuses - filter by status
     * @return array<int, array{
     *      id: string,
     *      type: string,
     *      title: string,
     *      company: array{
     *          id: string,
     *          name: string,
     *          logo?: array{
     *              name: string,
     *              mime: string
     *          },
     *      },
     *      description?: string,
     *      startDate: \DateTimeImmutable,
     *      rejectionReason?: string,
     *      minutes: int
     * }>
     */
    #[Override]
    public function getInterviewAgenda(
        string $candidateId,
        DateTimeImmutable $date,
        int $skip = 0,
        int $limit = 15,
        array $statuses = [],
    ): array
    {
        //-- Query building
        $dayStart = $date->setTime(0, 0, 0);
        $dayEnd =  $dayStart->modify('+1 day');

        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('i', 'a', 'c')
            ->from(InterviewEntity::class, 'i')
            ->innerJoin('i.application', 'a')
            ->innerJoin('a.company', 'c')
            ->where('a.candidate = :candidateId')
            ->andWhere('i.startDate >= :dayStart')
            ->andWhere('i.startDate < :dayEnd')
            ->orderBy('i.startDate', 'ASC')
            ->setFirstResult($skip)
            ->setMaxResults($limit)
                ->setParameters([
                'candidateId' => $candidateId,
                'dayStart' => $dayStart,
                'dayEnd' => $dayEnd,
            ]);;

        //-- Treat statuses
        if(!empty($statuses)){
            $qb->andWhere("i.status in (:interviewStatuses)")
               ->setParameter("interviewStatuses", $statuses);
        }


        $interviews = $qb->getQuery()
                         ->getResult();

        return array_map(static function (InterviewEntity $interview): array {
            $application = $interview->getApplication();
            $company = $application->getCompany();
            $logo = $company->getLogo();

            $data = [
                'id' => $interview->getId(),
                'title' =>$interview->getTitle(),
                'type' => $interview->getType(),
                'company' => [
                    'id' => $company->getId(),
                    'name' => $company->getName(),
                ],
                'startDate' => $interview->getStartDate()->format(\DateTimeInterface::ATOM),
                'minutes' => $interview->getMinutes(),
                'rejectionReason'=> $interview->getRejectionReason()
            ];

            if ($interview->getDescription() !== null) {
                $data['description'] = $interview->getDescription();
            }

            if ($logo !== null) {
                $data['company']['logo'] = [
                    'name' => $logo->getName(),
                    'mime' => $logo->getMime()
                ];
            }

            return $data;
        }, $interviews);
    }


    /**
     * Retrieve the user's interviews for a given month, grouped by day.
     *
     * @return array<string, array<int, array{
     *     id: string,
     *     startDate: string,
     *     title: ?string,
     *     type: ?InterviewType,
     *     status: InterviewStatus
     * }>>
     *
     * The array key represents the interview day using the `Y-m-d` format.
     */
    #[Override]
    public function getCalendarCollectionViews(string $candidateId, DateTimeImmutable $month): array
    {
        $startOfMonth = $month->modify("first day of this month")
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
            ->andWhere('i.startDate >= :startOfMonth')
            ->andWhere('i.startDate < :startOfNextMonth')
            ->andWhere('a.candidate = :candidateId')
            ->setParameter('candidateId', $candidateId)
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
}