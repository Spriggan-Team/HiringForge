<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Interview\Repositories;

use App\Domain\Interviews\Interview;
use App\Domain\Interviews\InterviewContext;
use App\Domain\Interviews\InterviewsRepositoryInterface;
use App\Domain\Interviews\InterviewStatus;
use App\Domain\Interviews\InterviewType;

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


    /**
     * Count Interviews
     */
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