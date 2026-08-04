<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate\Repositories;

use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\Exception\ApplicationNotFoundException;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;


use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;


use Override;


class JobOfferApplicationRepository
    extends ServiceEntityRepository
    implements ApplicationRepositoryInterface
{

    public function __construct(
        ManagerRegistry $registry
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
    public function count(array $criteria): int 
        {
            $qb = $this->createQueryBuilder('a')
                ->select('COUNT(DISTINCT a.id)');

            //  Filter by Job Posting
            if (isset($criteria['jobOfferId'])) {
                $qb->andWhere('a.jobOffer = :jobOfferId')
                ->setParameter('jobOfferId', $criteria['jobOfferId']);
            }

            // Filter by application status (Enum or String)
            if (isset($criteria['status'])) {
                $qb->andWhere('a.status = :status')
                ->setParameter('status', $criteria['status']);
            }

            //  Filter by company (using the JobOffer -> Company relationship)
            if (isset($criteria['companyId'])) {
                $qb->join('a.jobOffer', 'j')
                    ->andWhere('j.company = :companyId') 
                    ->setParameter('companyId', $criteria['companyId']);
            }

            return (int) $qb->getQuery()->getSingleScalarResult();
        }
}