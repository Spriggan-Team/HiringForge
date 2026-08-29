<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Repositories\Queries;


use App\Domain\JobOffer\JobPublicationStatus;
use App\Application\Query\JobOffer\Repositories\PublicJobOfferQueryRepositoryInterface;


use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferImageEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferLanguageEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferSkillsEntity;


use Override;
use Doctrine\ORM\EntityManagerInterface;


/**
 * Doctrine-based read repository dedicated to job offer queries.
 *
 * This repository is responsible only for data retrieval optimized
 * for presentation or read use cases (queries).
 * 
 * It does NOT reconstruct domain aggregates and must not contain
 * business logic. Its purpose is to return lightweight projections
 * tailored for application query handlers.
 */

class PublicJobOfferQueryRepository implements PublicJobOfferQueryRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $manager,
    ){}

    
    public function fetchPublicJobOffersSummary(
        string $locale = 'fr',
        ?string $search = null,
        ?string $address = null,
        int $limit = 10,
        int $skip = 0
    ): array {
        $qb = $this->manager->createQueryBuilder()
            ->select(
                'j.id',
                'j.title',
                'j.content',
                'j.jobWorkMode',
                'j.minSalary',
                'j.maxSalary',
                'j.currency',
                'ct.id AS contractId',
                'ct.label AS contractLabel',
                'a.street',
                'a.postalCode',
                'a.city',
                'a.country',
                'f.name AS imageName',
                'f.mime AS imageMime'
            )
            ->from(JobOfferEntity::class, 'j')
            ->leftJoin('j.contractType', 'ct')
            ->leftJoin('j.address', 'a')
            ->leftJoin(
                'j.images',
                'img',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'img.isMain = true'
            )
            ->leftJoin('img.file', 'f')
            ->where('j.publicationStatus = :publishedStatus')
            ->setParameter('publishedStatus', JobPublicationStatus::PUBLISHED);

        if (!empty($search)) {
            $qb->andWhere('LOWER(j.title) LIKE :search OR LOWER(j.content) LIKE :search')
               ->setParameter('search', '%' . mb_strtolower($search) . '%');
        }

        if (!empty($address)) {
            $qb->andWhere('LOWER(a.city) LIKE :addr OR LOWER(a.country) LIKE :addr OR LOWER(a.postalCode) LIKE :addr')
               ->setParameter('addr', '%' . mb_strtolower($address) . '%');
        }

        // --- Skip & Limit ---
        $qb->setFirstResult($skip)
           ->setMaxResults($limit);

        // Count total items
        $countQb = clone $qb;
        $countQb->resetDQLPart('select')
                ->select('COUNT(DISTINCT j.id)')
                ->setFirstResult(null)
                ->setMaxResults(null);
        $totalCount = (int) $countQb->getQuery()->getSingleScalarResult();

        $results = $qb->getQuery()->getArrayResult();

        $items = array_map(static function (array $row): array {
            return [
                'id' => $row['id'],
                'title' => $row['title'],
                'content' => $row['content'],
                'jobWorkMode' => $row['jobWorkMode']?->value ?? $row['jobWorkMode'],
                'salary' => [
                    'min' => $row['minSalary'],
                    'max' => $row['maxSalary'],
                    'currency' => $row['currency'],
                ],
                'contractType' => $row['contractId'] ? [
                    'id' => $row['contractId'],
                    'label' => $row['contractLabel'],
                ] : null,
                'location' => [
                    'street' => $row['street'],
                    'postalCode' => $row['postalCode'],
                    'city' => $row['city'],
                    'country' => $row['country'],
                ],
                'mainImage' => $row['mainImage'] ?? null,
            ];
        }, $results);

        return [
            'items' => $items,
            'total' => $totalCount,
            'limit' => $limit,
            'skip' => $skip,
        ];
    }



    public function fetchPublicJobOfferDetail(
        string $jobOfferId,
        string $locale = 'fr'
    ): ?array {
        // Basic Information About the Offer
        $qb = $this->manager->createQueryBuilder()
            ->select(
                'j.id',
                'j.title',
                'j.content',
                'j.jobWorkMode',
                'j.expertise',
                'j.minSalary',
                'j.maxSalary',
                'j.currency',
                'ct.id AS contractId',
                'ct.label AS contractLabel',
                'a.street',
                'a.postalCode',
                'a.city',
                'a.country'
            )
            ->from(JobOfferEntity::class, 'j')
            ->leftJoin('j.contractType', 'ct')
            ->leftJoin('j.address', 'a')
            ->where('j.id = :jobId')
            ->andWhere('j.publicationStatus = :publishedStatus')
            ->setParameter('jobId', $jobOfferId)
            ->setParameter('publishedStatus', JobPublicationStatus::PUBLISHED);

        $offer = $qb->getQuery()->getOneOrNullResult();

        if (!$offer) {
            return null;
        }

        // Skills with translations based on the locale
        $skillsQb = $this->manager->createQueryBuilder()
            ->select('s.id', 'st.name', 'jos.isRequired')
            ->from(JobOfferSkillsEntity::class, 'jos')
            ->join('jos.skill', 's')
            ->leftJoin('s.translations', 'st')
            ->leftJoin('st.language', 'lang')
            ->where('jos.jobOffer = :jobId')
            ->setParameter('jobId', $jobOfferId);

        $skillsResult = $skillsQb->getQuery()->getArrayResult();

        $skills = array_map(static fn(array $skill) => [
            'id' => $skill['id'],
            'name' => $skill['name'] ?? '',
            'isRequired' => (bool) $skill['isRequired'],
        ], $skillsResult);

        //  Languages
        $languagesQb = $this->manager->createQueryBuilder()
            ->select('l.id', 'l.label AS name', 'jol.level')
            ->from(JobOfferLanguageEntity::class, 'jol')
            ->join('jol.language', 'l')
            ->where('jol.jobOffer = :jobId')
            ->setParameter('jobId', $jobOfferId);

        $languagesResult = $languagesQb->getQuery()->getArrayResult();

        $languages = array_map(static fn(array $lang) => [
            'id' => $lang['id'],
            'name' => $lang['name'],
            'level' => $lang['level']?->value ?? $lang['level'],
        ], $languagesResult);

        // Photos / Images
        $imagesQb = $this->manager->createQueryBuilder()
            ->select('f.name')
            ->from(JobOfferImageEntity::class, 'img')
            ->innerJoin('img.file', 'f')
            ->where('img.jobOffer = :jobId')
            ->setParameter('jobId', $jobOfferId);

        $imagesResult = array_column($imagesQb->getQuery()->getArrayResult(), 'url');

        // Final Assembly of the Response Contract
        return [
            'id' => $offer['id'],
            'title' => $offer['title'],
            'content' => $offer['content'],
            'jobWorkMode' => $offer['jobWorkMode']?->value ?? $offer['jobWorkMode'],
            'expertise' => $offer['expertise']?->value ?? $offer['expertise'],
            'salary' => [
                'min' => $offer['minSalary'],
                'max' => $offer['maxSalary'],
                'currency' => $offer['currency'],
            ],
            'contractType' => $offer['contractId'] ? [
                'id' => $offer['contractId'],
                'label' => $offer['contractLabel'],
            ] : null,
            'location' => [
                'street' => $offer['street'],
                'postalCode' => $offer['postalCode'],
                'city' => $offer['city'],
                'country' => $offer['country'],
            ],
            'skills' => $skills,
            'languages' => $languages,
            'images' => $imagesResult,
        ];
    }

 
}