<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Interview\Repositories;

use App\Domain\Interviews\InterviewsRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity;

use Override;

use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class InterviewsRepository extends ServiceEntityRepository 
    implements InterviewsRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InterviewEntity::class);
    }
        
    public function count(array $criteria): int 
    {
        $qb = $this->createQueryBuilder('i')
            ->select('COUNT(DISTINCT i.id)');

        //-- Filter by status
        if (!empty($criteria['status'])) {
            $qb->andWhere('i.status = :status')
               ->setParameter('status', $criteria['status']);
        }

        // Direct filter : check if an iterviews posses a relation with the procided offer
        if (!empty($criteria['jobOfferId'])) {
            $qb->andWhere('i.jobOffer = :jobOfferId')
               ->setParameter('jobOfferId', $criteria['jobOfferId']);
        }

        // Filter By Compoany par Company (companyId)
        if (!empty($criteria['companyId'])) {
            $qb->innerJoin('i.jobOffer', 'j');
  
            $qb->andWhere('j.company = :companyId')
                ->setParameter('companyId', $criteria['companyId']);
        }

        try {
            return (int) $qb->getQuery()->getSingleScalarResult();
        }
        catch (NoResultException | NonUniqueResultException) {
            return 0;
        }
    }
}