<?php


namespace App\Infrastructure\Persistence\Doctrine\ORM\Offer\Repositories;

use App\Domain\Offer\Offer;
use App\Domain\Offer\OfferRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Offer\OfferEntity;


use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use OfferRepositoryMapper;
use Override;


class OfferRepository extends ServiceEntityRepository 
    implements OfferRepositoryInterface
{
    public function __construct(
        private ManagerRegistry $registry,
        private OfferRepositoryMapper $mapper
    ){
        parent::__construct($registry, OfferEntity::class);
    }

    
    #[Override]
    public function createOffer(string $userId, Offer $offer): void
    {
        $em = $this->getEntityManager();
        $entity = $this->mapper->toEntity($offer);

        //-- Saving
        $em->persist($entity);
        $em->flush();
    }


    #[Override]
    public function fetchOfferProjection(
        ?string $userId = null,
        ?string $companyId = null,
        ?string $jobId = null,
        array $scheme = ['id' => true],
        int $limit = 17,
        int $skip = 0
    ): array {
        $qb = $this->createQueryBuilder('o');

        // 1. OfferEntity Projection (Adding Salary)
        $offerFields = ['id', 'title', 'message', 'salary', 'status', 'expiredAt', 'sentAt'];
        $selects = array_map(
            fn(string $field) => "o.$field",
            array_filter($offerFields, fn(string $field) => !empty($scheme[$field]))
        );

        //  Candidate & Image Screening
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
                    $dbField = $field === 'mime' ? 'mimeType' : $field;
                    $selects[] = "img.$dbField AS candidate_image_$field";
                }
            }
        }

        // Links to Application and JobOffer
        $qb->innerJoin('o.application', 'a')
        ->innerJoin('a.jobOffer', 'j');

        //  Projection Application
        $applicationScheme = array_filter($scheme['application'] ?? []);
        if (!empty($applicationScheme['id'])) {
            $selects[] = 'a.id AS application_id';
        }

        //  Projection JobOffer
        $jobOfferScheme = array_filter($scheme['jobOffer'] ?? []);
        if (!empty($jobOfferScheme)) {
            foreach (['id', 'title'] as $field) {
                if (!empty($jobOfferScheme[$field])) {
                    $selects[] = "j.$field AS jobOffer_$field";
                }
            }
        }

        // Fallbackif nothing is selected
        $qb->select($selects ?: ['o.id']);

        // Filtres
        if ($companyId !== null) {
            $qb->where('a.company = :companyId')
            ->setParameter('companyId', $companyId);
        } elseif ($userId !== null) {
            $qb->where('j.user = :userId')
            ->setParameter('userId', $userId);
        }

        if ($jobId !== null) {
            $qb->andWhere('j.id = :jobId')
            ->setParameter('jobId', $jobId);
        }

        $qb->setFirstResult($skip)
        ->setMaxResults($limit);

        $results = $qb->getQuery()->getArrayResult();

        // Reformatting Nested Results
        if (!empty($candidateScheme) || !empty($applicationScheme) || !empty($jobOfferScheme)) {
            return array_map(function (array $row) {
                $formatted = [];
                foreach ($row as $key => $value) {
                    if (str_starts_with($key, 'candidate_image_')) {
                        $formatted['candidate']['image'][substr($key, 16)] = $value;
                    } elseif (str_starts_with($key, 'candidate_')) {
                        $formatted['candidate'][substr($key, 10)] = $value;
                    } elseif (str_starts_with($key, 'application_')) {
                        $formatted['application'][substr($key, 12)] = $value;
                    } elseif (str_starts_with($key, 'jobOffer_')) {
                        $formatted['jobOffer'][substr($key, 9)] = $value;
                    } else {
                        $formatted[$key] = $value;
                    }
                }
                return $formatted;
            }, $results);
        }

        return $results;
    }
}
