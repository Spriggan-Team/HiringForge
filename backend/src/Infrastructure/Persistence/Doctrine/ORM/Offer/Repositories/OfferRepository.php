<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Offer\Repositories;

use App\Domain\Offer\Offer;
use App\Domain\Offer\OfferRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Offer\OfferEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Offer\Repositories\OfferRepositoryMapper as RepositoriesOfferRepositoryMapper;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use OfferRepositoryMapper;
use Override;


class OfferRepository extends ServiceEntityRepository 
    implements OfferRepositoryInterface
{
    public function __construct(
        private ManagerRegistry $registry,
        private RepositoriesOfferRepositoryMapper $mapper
    ){
        parent::__construct($registry, OfferEntity::class);
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
    public function save(string $userId, Offer $offer): void
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

        // 1. Projection Offer (Champs direct de l'entité o)
        $offerFields = ['id', 'title', 'message', 'salary', 'status', 'expiredAt', 'sentAt'];
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
                    $dbField = ($field === 'mime') ? 'mime' : $field;
                    $selects[] = "img.$dbField AS candidate_image_$field";
                }
            }
        }

        // 3. Application & JobOffer
        $qb->leftJoin('o.application', 'a')
           ->leftJoin('a.jobOffer', 'j');

        // Projection Application
        $applicationScheme = array_filter($scheme['application'] ?? []);
        if (!empty($applicationScheme['id'])) {
            $selects[] = 'a.id AS application_id';
        }

        // Projection JobOffer
        $jobOfferScheme = array_filter($scheme['jobOffer'] ?? []);
        if (!empty($jobOfferScheme)) {
            foreach (['id', 'title'] as $field) {
                if (!empty($jobOfferScheme[$field])) {
                    $selects[] = "j.$field AS jobOffer_$field";
                }
            }
        }

        // Secours si aucun champ sélectionné
        if (empty($selects)) {
            $selects[] = 'o.id AS id';
        }

        $qb->select($selects);

        // 4. Gestion propre des filtres WHERE (Résout le bug du SQL invalide)
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

        // 5. Pagination & Exécution
        $qb->setFirstResult($skip)
           ->setMaxResults($limit);

        $results = $qb->getQuery()->getArrayResult();

        // 6. Reformatage de la structure imbriquée
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
