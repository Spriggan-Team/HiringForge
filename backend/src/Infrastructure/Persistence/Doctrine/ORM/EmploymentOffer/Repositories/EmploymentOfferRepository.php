<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\EmploymentOffer\Repositories;

use App\Domain\EmploymentOffer\EmploymentOffer as DomainEmploymentOffer;
use App\Domain\EmploymentOffer\EmploymentOfferRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\EmploymentOffer\EmploymentOfferEntity;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Override;


class EmploymentOfferRepository extends ServiceEntityRepository 
    implements EmploymentOfferRepositoryInterface
{
    public function __construct(
        private ManagerRegistry $registry,
        private EmploymentOfferRepositoryMapper $mapper
    ){
        parent::__construct($registry, EmploymentOfferEntity::class);
    }


    #[Override]
    public function canCreateEmploymentOfferForApplication(
        string $recruiterId,
        string $applicationId,
        string $candidateId
    ): bool {
        $now = new \DateTimeImmutable();

        $activeOffersCount = (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->innerJoin('e.application', 'a')
            ->innerJoin('e.candidate', 'c')
            ->where('a.id = :applicationId')
            ->andWhere('c.id = :candidateId')
            ->andWhere('e.expiredAt > :now')
            ->andWhere('e.confirm IS NULL OR e.confirm = true') // not false, not rejected
            ->setParameter('applicationId', $applicationId)
            ->setParameter('candidateId', $candidateId)
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();

        //-- no employment offer active
        return $activeOffersCount === 0;
    }


    #[Override]
    public function countOffers(array $criteria): int
    {
        $qb = $this->createQueryBuilder('o')
            ->select('COUNT(DISTINCT o.id)');

        // Direct filtering by offer status (e.g., OfferStatus::ACCEPTED, OfferStatus::DECLINED, OfferStatus::SENT)
        if (isset($criteria['status'])) {
            $qb->andWhere('o.status = :status')
               ->setParameter('status', $criteria['status']);
        }

        // Direct Screening by Application
        if (isset($criteria['applicationId'])) {
            $qb->andWhere('o.application = :applicationId')
               ->setParameter('applicationId', $criteria['applicationId']);
        }

        // Filter by Candidate
        if (isset($criteria['candidateId'])) {
            $qb->andWhere('o.candidate = :candidateId')
               ->setParameter('candidateId', $criteria['candidateId']);
        }

        // If you search by JobOffer or by Recruiter (User), you must perform a join with Application and JobOffer
        if (isset($criteria['jobOfferId']) || isset($criteria['userId'])) {
            $qb->innerJoin('o.application', 'a')
               ->innerJoin('a.jobOffer', 'j');

            if (isset($criteria['jobOfferId'])) {
                $qb->andWhere('j.id = :jobOfferId')
                   ->setParameter('jobOfferId', $criteria['jobOfferId']);
            }

            if (isset($criteria['userId'])) {
                $qb->andWhere('j.user = :userId')
                   ->setParameter('userId', $criteria['userId']);
            }
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    
    #[Override]
    public function save(string $userId, DomainEmploymentOffer $offer): void
    {
        $em = $this->getEntityManager();

        $existingEntity = null;
        if ($offer->id() !== null) {
            $existingEntity = $this->find($offer->id());
        }

        //-- Transfom
        $entity = $this->mapper->toEntity($offer, $existingEntity);

        // New entity
        if ($existingEntity === null) {
            $em->persist($entity);
        }

        //  Flush
        $em->flush();
    }


    #[Override]
    /**
     * Returns an array of offers related to a recruiter.
     *
     * @param ?string $userId The unique identifier of the user.
     * @param ?string $companyId The unique identifier of the company.
     * @param ?string $jobId The unique identifier of a job.
     * @param array $scheme Defines which fields should be returned.
     * @param int $limit
     * @param int $skip
     * @return array
     */
    public function fetchOfferProjection(
        ?string $userId = null,
        ?string $companyId = null,
        ?string $jobId = null,
        array $scheme = ['id' => true],
        int $limit = 17,
        int $skip = 0
    ): array {
        $qb = $this->createQueryBuilder('o');
        $selects = [];

        // 1. Projection Offer (Champs corrects de EmploymentOfferEntity)
        $offerFields = ['id', 'message', 'salary', 'status', 'expiredAt', 'createdAt']; // 👈 'sentAt' remplacé par 'createdAt'
        foreach ($offerFields as $field) {
            if (!empty($scheme[$field])) {
                $selects[] = "o.$field AS $field";
            }
        }

        // 2. Candidate & Image
        $candidateScheme = $scheme['candidate'] ?? [];
        if (!empty($candidateScheme)) {
            $qb->leftJoin('o.candidate', 'c');

            foreach (['id', 'firstName', 'lastName', 'email'] as $field) {
                if (!empty($candidateScheme[$field])) {
                    $selects[] = "c.$field AS candidate_$field";
                }
            }

            $imageScheme = array_filter($candidateScheme['image'] ?? []);
            if (!empty($imageScheme)) {
                $qb->leftJoin('c.image', 'img');
                foreach (array_keys($imageScheme) as $field) {
                    $selects[] = "img.$field AS candidate_image_$field";
                }
            }
        }

        // 3. Application & JobOffer
        $qb->leftJoin('o.application', 'a')
        ->leftJoin('a.jobOffer', 'j');

        $applicationScheme = array_filter($scheme['application'] ?? []);
        if (!empty($applicationScheme['id'])) {
            $selects[] = 'a.id AS application_id';
        }

        $jobOfferScheme = array_filter($scheme['jobOffer'] ?? []);
        if (!empty($jobOfferScheme)) {
            foreach (['id', 'title'] as $field) {
                if (!empty($jobOfferScheme[$field])) {
                    $selects[] = "j.$field AS jobOffer_$field";
                }
            }
        }

        if (empty($selects)) {
            $selects[] = 'o.id AS id';
        }

        $qb->select($selects);

        // 4. Conditions de filtrage
        $conditions = [];

        if (!empty($companyId)) {
            $conditions[] = $qb->expr()->eq('a.company', ':companyId');
            $qb->setParameter('companyId', $companyId);
        } elseif (!empty($userId)) {
            $conditions[] = $qb->expr()->eq('j.user', ':userId');
            $qb->setParameter('userId', $userId);
        }

        if (!empty($jobId)) {
            $conditions[] = $qb->expr()->eq('j.id', ':jobId');
            $qb->setParameter('jobId', $jobId);
        }

        if (count($conditions) > 0) {
            $qb->where(...$conditions);
        }

        // 5. Pagination
        $qb->setFirstResult($skip)
        ->setMaxResults($limit);

        $results = $qb->getQuery()->getArrayResult();

        // 6. Restructuration des clés pour l'output DTO/JSON
        return array_map(function (array $row) {
            $formatted = [];
            
            foreach ($row as $key => $value) {
                if (str_starts_with($key, 'candidate_image_')) {
                    $field = substr($key, 16);
                    $formatted['candidate']['image'][$field] = $value;
                } elseif (str_starts_with($key, 'candidate_')) {
                    $field = substr($key, 10);
                    $formatted['candidate'][$field] = $value;
                } elseif (str_starts_with($key, 'application_')) {
                    $field = substr($key, 12);
                    $formatted['application'][$field] = $value;
                } elseif (str_starts_with($key, 'jobOffer_')) {
                    $field = substr($key, 9);
                    $formatted['jobOffer'][$field] = $value;
                } else {
                    $formatted[$key] = $value;
                }
            }

            return $formatted;
        }, $results);
    }
}
