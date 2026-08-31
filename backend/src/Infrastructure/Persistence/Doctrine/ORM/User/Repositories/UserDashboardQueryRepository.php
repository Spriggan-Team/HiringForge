<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\User\Repositories;

use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Application\Query\User\Repositories\UserDashboardQueryRepositoryInterface;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity;

use Doctrine\ORM\EntityManagerInterface;
use Override;


class UserDashboardQueryRepository implements UserDashboardQueryRepositoryInterface
{

    public function __construct(
        private EntityManagerInterface $em
    )
    {}


    #[Override]
    public function getDashboardStats(
        string $recruiterId,
        \DateTimeImmutable $date,
        string $timeframe = "week"
    ): array {
        [$startDate, $endDate, $timelineSize] = match ($timeframe) {
            'week' => $this->getWeekPeriod($date),
            'month' => $this->getMonthPeriod($date),
            'year' => $this->getYearPeriod($date),
            default => throw new \InvalidArgumentException(
                sprintf('Unsupported timeframe "%s".', $timeframe)
            ),
        };

        $applications = array_fill(0, $timelineSize, 0);
        $interviews = array_fill(0, $timelineSize, 0);
        $hires = array_fill(0, $timelineSize, 0);

        // -------------------------
        // Applications
        // -------------------------

        $applicationResults = $this->em
            ->createQueryBuilder()
            ->select(
                'a.appliedAt AS date',
                'COUNT(a.id) AS total'
            )
            ->from(ApplicationEntity::class, 'a')
            ->innerJoin('a.jobOffer', 'j')
            ->where('j.user = :recruiterId')
            ->andWhere('a.appliedAt >= :startDate')
            ->andWhere('a.appliedAt < :endDate')
            ->groupBy('a.appliedAt')
            ->setParameters([
                'recruiterId' => $recruiterId,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->getQuery()
            ->getArrayResult();

        // -------------------------
        // Interviews
        // -------------------------

        $interviewResults = $this->em
            ->createQueryBuilder()
            ->select(
                'i.createdAt AS date',
                'COUNT(i.id) AS total'
            )
            ->from(InterviewEntity::class, 'i')
            ->innerJoin('i.application', 'a')
            ->innerJoin('a.jobOffer', 'j')
            ->where('j.user = :recruiterId')
            ->andWhere('i.createdAt >= :startDate')
            ->andWhere('i.createdAt < :endDate')
            ->groupBy('i.createdAt')
            ->setParameters([
                'recruiterId' => $recruiterId,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->getQuery()
            ->getArrayResult();

        // -------------------------
        // Hires
        // -------------------------

        $hireResults = $this->em
            ->createQueryBuilder()
            ->select(
                'a.updatedAt AS date',
                'COUNT(a.id) AS total'
            )
            ->from(ApplicationEntity::class, 'a')
            ->innerJoin('a.jobOffer', 'j')
            ->where('j.user = :recruiterId')
            ->andWhere('a.status = :hiredStatus')
            ->andWhere('a.updatedAt >= :startDate')
            ->andWhere('a.updatedAt < :endDate')
            ->groupBy('a.updatedAt')
            ->setParameters([
                'recruiterId' => $recruiterId,
                'hiredStatus' => JobApplicationStatus::HIRED,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->getQuery()
            ->getArrayResult();

        // -------------------------
        // Fill timeline
        // -------------------------

        foreach ($applicationResults as $row) {
            $index = $this->getTimelineIndex(
                $row['date'],
                $startDate,
                $timeframe
            );

            if ($index !== null && isset($applications[$index])) {
                $applications[$index] += (int) $row['total'];
            }
        }

        foreach ($interviewResults as $row) {
            $index = $this->getTimelineIndex(
                $row['date'],
                $startDate,
                $timeframe
            );

            if ($index !== null && isset($interviews[$index])) {
                $interviews[$index] += (int) $row['total'];
            }
        }

        foreach ($hireResults as $row) {
            $index = $this->getTimelineIndex(
                $row['date'],
                $startDate,
                $timeframe
            );

            if ($index !== null && isset($hires[$index])) {
                $hires[$index] += (int) $row['total'];
            }
        }

        return [
            'applications' => $applications,
            'interviews' => $interviews,
            'hires' => $hires,
            
            'applicationCount' => array_sum($applications),
            'interviewsCount' => array_sum($interviews),
            'hiredCandidateCount' => array_sum($hires),
        ];
    }

    //---------------------
    //--- Helpers
    //---------------------

    private function getYearPeriod(
        \DateTimeImmutable $date
    ): array {
        $startDate = $date
            ->modify('first day of january')
            ->setTime(0, 0);

        $endDate = $startDate->modify('+1 year');

        return [
            $startDate,
            $endDate,
            12,
        ];
    }

    private function getWeekPeriod(
        \DateTimeImmutable $date
    ): array {
        // first day
        $startDate = $date
            ->modify('monday this week')
            ->setTime(0, 0);

        // start of following week
        $endDate = $startDate->modify('+7 days');

        return [
            $startDate,
            $endDate,
            7
        ];
    }


    private function getMonthPeriod(
        \DateTimeImmutable $date
    ): array {
        // First day of month
        $startDate = $date
            ->modify('first day of this month')
            ->setTime(0, 0);

        // First day of following month
        $endDate = $startDate->modify('+1 month');

        // Number of days in month
        $days = (int) $startDate->format('t');

        return [
            $startDate,
            $endDate,
            $days
        ];
    }

        //-- Format
    private function getTimelineIndex(
        \DateTimeInterface|string $date,
        \DateTimeImmutable $startDate,
        string $timeframe
    ): ?int {
        if (is_string($date)) {
            $date = new \DateTimeImmutable($date);
        }

        $date = \DateTimeImmutable::createFromInterface($date);

        return match ($timeframe) {
            'week',
            'month' => (int) $startDate
                ->setTime(0, 0)
                ->diff($date->setTime(0, 0))
                ->format('%r%a'),

            'year' => (int) $date->format('n') - 1,
            default => null,
        };
    }
}