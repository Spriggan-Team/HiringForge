<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\EmploymentOffer\Repositories;

use App\Domain\EmploymentOffer\CandidateEmploymentOfferReaderInterface;
use App\Domain\EmploymentOffer\EmploymentOfferMenu;
use App\Domain\EmploymentOffer\EmploymentOfferStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\EmploymentOffer\EmploymentOfferEntity;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Override;

/**
 * Specialize in  reading data about employment offer
 */
class CandidateEmploymentOfferReader 
    extends ServiceEntityRepository
    implements CandidateEmploymentOfferReaderInterface
{
    public function __construct(
        ManagerRegistry $registry
    ){
        parent::__construct($registry, EmploymentOfferEntity::class);
    }

        
    /**
     * Retrieve KPI/stats data about user's employment offers.
     *
     * @return array{
     *     awaiting: int,
     *     accepted: int,
     *     rejected: int,
     *     completed: int,
     * }
     */
    public function fetchEmploymentOffersStatsForCandidate(
        string $candidateId
    ): array {
        $today = new \DateTimeImmutable();

        $result = $this->createQueryBuilder('o')
            ->innerJoin('o.application', 'oa')
            ->where('oa.candidate = :candidateId')
            ->select(
                'SUM(
                    CASE
                        WHEN o.status = :awaitingStatus
                        THEN 1
                        ELSE 0
                    END
                ) AS awaiting',

                'SUM(
                    CASE
                        WHEN o.status = :acceptedStatus
                        THEN 1
                        ELSE 0
                    END
                ) AS accepted',

                'SUM(
                    CASE
                        WHEN o.status = :declinedStatus
                        THEN 1
                        ELSE 0
                    END
                ) AS rejected',

                'SUM(
                    CASE
                        WHEN o.status = :acceptedStatus
                        AND o.scheduledEndDate < :today
                        THEN 1
                        ELSE 0
                    END
                ) AS completed'
            )
            ->setParameter('awaitingStatus', EmploymentOfferStatus::SENT)
            ->setParameter('acceptedStatus', EmploymentOfferStatus::ACCEPTED)
            ->setParameter('declinedStatus', EmploymentOfferStatus::DECLINED)
            ->setParameter('today', $today)
            ->setParameter('candidateId', $candidateId)
            ->getQuery()
            ->getSingleResult();

            
        return [
            'awaiting' => (int) ($result['awaiting'] ?? 0),
            'accepted' => (int) ($result['accepted'] ?? 0),
            'rejected' => (int) ($result['rejected'] ?? 0),
            'completed' => (int) ($result['completed'] ?? 0),
        ];
    }

    /**
     * Retrieve employment offers for a candidate.
     *
     * @param array{
     *     jobTitle?: string,
     *     menu: EmploymentOfferMenu
     * } $criteria
     *
     * @return array{
     *     data: array<int, array{
     *         id: string,
     *         status: EmploymentOfferStatus,
     *         salary: float|null,
     *         message: string|null,
     *         application: array{
     *             id: string
     *         },
     *         jobOffer: array{
     *             id: string,
     *             title: string
     *         },
     *         company: array{
     *             id: string,
     *             name: string,
     *             logo: array{
     *                 id: int,
     *                 name: string,
     *                 mime: string
     *             }|null
     *         },
     *         rejectionReason: string|null,
     *         scheduledEndDate: string,
     *         expiredAt: string,
     *         createdAt: string
     *     }>,
     *     total: int
     * }
     */
    #[Override]
    public function fetchEmploymentOffersForCandidate(
        string $candidateId,
        array $criteria = [],
        int $skip = 0,
        int $limit = 15
    ): array {
        $menu = $criteria['menu'] ?? EmploymentOfferMenu::ALL;

        if (!$menu instanceof EmploymentOfferMenu) {
            $menu = EmploymentOfferMenu::tryFrom((string) $menu)
                ?? EmploymentOfferMenu::ALL;
        }

        $jobTitle = isset($criteria['jobTitle'])
            ? trim((string) $criteria['jobTitle'])
            : null;

        /*
        * =========================
        * TOTAL
        * =========================
        */
        $countQuery = $this->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->innerJoin('o.application', 'oa')
            ->innerJoin('oa.jobOffer', 'j')
            ->where('oa.candidate = :candidateId')
            ->setParameter('candidateId', $candidateId);

        $this->applyEmploymentOfferFilters(
            query: $countQuery,
            jobTitle: $jobTitle,
            menu: $menu
        );

        $total = (int) $countQuery
            ->getQuery()
            ->getSingleScalarResult();


        /*
        * =========================
        * DATA
        * =========================
        */
        $query = $this->createQueryBuilder('o')
            ->select(
                'o.id',
                'o.status',
                'o.message',
                'oa.id AS applicationId',
                'o.rejectionReason',
                'o.salary',
                'o.expiredAt',
                'o.scheduledEndDate',
                'o.createdAt',
                'j.id AS jobId',
                'j.title AS jobTitle',
                'c.id AS companyId',
                'c.name AS companyName',
                'cl.id AS logoId',
                'cl.name AS logoName',
                'cl.mime AS logoMime'
            )
            ->innerJoin('o.application', 'oa')
            ->innerJoin('oa.jobOffer', 'j')
            ->innerJoin('oa.company', 'c')
            ->leftJoin('c.logo', 'cl')
            ->where('oa.candidate = :candidateId')
            ->setParameter('candidateId', $candidateId);

        $this->applyEmploymentOfferFilters(
            query: $query,
            jobTitle: $jobTitle,
            menu: $menu
        );

        $rows = $query
            ->setFirstResult(max(0, $skip))
            ->setMaxResults(max(1, $limit))
            ->orderBy('o.createdAt', 'DESC')
            ->getQuery()
            ->getArrayResult();


        /*
        * =========================
        * MAPPING
        * =========================
        */
        $data = array_map(
            static function (array $row): array {
                return [
                    'id' => $row['id'],
                    'status' => $row['status'],
                    'salary' => $row['salary'],
                    'message' => $row['message'],

                    'application' => [
                        'id' => $row['applicationId'],
                    ],

                    'jobOffer' => [
                        'id' => $row['jobId'],
                        'title' => $row['jobTitle'],
                    ],

                    'company' => [
                        'id' => $row['companyId'],
                        'name' => $row['companyName'],
                        'logo' => $row['logoId'] !== null
                            ? [
                                'id' => $row['logoId'],
                                'name' => $row['logoName'],
                                'mime' => $row['logoMime'],
                            ]
                            : null,
                    ],

                    'rejectionReason' => $row['rejectionReason'],
                    'scheduledEndDate' => $row['scheduledEndDate'],
                    'expiredAt' => $row['expiredAt'],
                    'createdAt' => $row['createdAt'],
                ];
            },
            $rows
        );

        return [
            'data' => $data,
            'total' => $total,
        ];
    }


    /**
     * --------------------------
     * Helpers
     * -----------------------------
     */

    /**
     * @param QueryBuilder $query
     * @param string|null $jobTitle
     */
    private function applyEmploymentOfferFilters(
        QueryBuilder $query,
        ?string $jobTitle,
        EmploymentOfferMenu $menu
    ): void {
        if ($jobTitle !== null && $jobTitle !== '') {
            $query
                ->andWhere('LOWER(j.title) LIKE LOWER(:jobTitle)')
                ->setParameter(
                    'jobTitle',
                    '%' . $jobTitle . '%'
                );
        }

        switch ($menu) {
            case EmploymentOfferMenu::PENDING:
                $query
                    ->andWhere('o.status = :pendingStatus')
                    ->setParameter(
                        'pendingStatus',
                        EmploymentOfferStatus::SENT
                    );
                break;

            case EmploymentOfferMenu::ACCEPTED:
                $query
                    ->andWhere('o.status = :acceptedStatus')
                    ->andWhere('o.scheduledEndDate > :today')
                    ->setParameter(
                        'acceptedStatus',
                        EmploymentOfferStatus::ACCEPTED
                    )
                    ->setParameter(
                        'today',
                        new \DateTimeImmutable()
                    );
                break;

            case EmploymentOfferMenu::COMPLETED:
                $query
                    ->andWhere('o.status = :completedStatus')
                    ->andWhere('o.scheduledEndDate <= :today')
                    ->setParameter(
                        'completedStatus',
                        EmploymentOfferStatus::ACCEPTED
                    )
                    ->setParameter(
                        'today',
                        new \DateTimeImmutable()
                    );
                break;

            case EmploymentOfferMenu::REJECTED:
                $query
                    ->andWhere('o.status = :rejectedStatus')
                    ->setParameter(
                        'rejectedStatus',
                        EmploymentOfferStatus::DECLINED
                    );
                break;

            case EmploymentOfferMenu::ALL:
                break;
        }
    }


}