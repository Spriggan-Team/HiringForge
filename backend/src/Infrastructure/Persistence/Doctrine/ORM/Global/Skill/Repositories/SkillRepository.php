<?php

namespace  App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\Repositories;

use App\Domain\Shared\Skill\Skill;
use App\Domain\Exception\RessourceNotFound;
use App\Domain\Shared\Skill\SkillRepositoryInterface;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillAliasEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillTranslationEntity;


use Override;
use Doctrine\ORM\EntityManagerInterface;


final class SkillRepository
    implements SkillRepositoryInterface
{

    public function __construct(
        private EntityManagerInterface $em,
        private SkillMapper $mapper
    ){}


    #[Override]
    public function get(string $id): Skill
    {
        $entity = $this->em
            ->getRepository(SkillEntity::class)
            ->find($id);

        if(!$entity){
            throw new RessourceNotFound(
                "Skill not found"
            );
        }

        return $this->mapper->toDomain($entity);
    }


    #[Override]
    /** 
     * @param array<string, mixed> $scheme Defines the expected output structure/fields.
     * @return array<int, array<string, mixed>>
     */
    public function fetchAssociativeArray(string $text, string $locale, array $scheme): array
    {
        $text = mb_strtolower(trim($text));
        $locale = substr(mb_strtolower(trim($locale)), 0, 2);
        if ($text === '') {
            return [];
        }

        $maxResults = 15;
        $searchTerm = '%' . $text . '%';
        $slugTerm = '%' . preg_replace('/[^a-z0-9]+/', '', $text) . '%';

        // -- Priority Search in Translations & Slugs
        $transRepo = $this->em->getRepository(SkillTranslationEntity::class);
        $selectedTranslationFields = $this->buildTranslationSelectFields($scheme);

        $translations = $transRepo->createQueryBuilder('st')
            ->select($selectedTranslationFields)
            ->join('st.skill', 's') 
            ->join('st.language', 'l')
            ->where('(LOWER(st.name) LIKE :search OR LOWER(st.slug) LIKE :slugSearch)')
            ->andWhere('l.code = :locale')
            ->setParameter('search', $searchTerm)
            ->setParameter('slugSearch', $slugTerm)
            ->setParameter('locale', $locale)
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getArrayResult();

        //-- Priority Search in Translations & Slugs
        $foundSkillIds = array_filter(array_column($translations, 'id'));

        $totalFound = count($translations);
        if ($totalFound >= $maxResults) {
            return $translations;
        }

        // 2. Additional Search in Aliases
        $remainingLimit = $maxResults - $totalFound;

        $aliasRepo = $this->em->getRepository(SkillAliasEntity::class);
        $selectedAliasFields = $this->buildAliasSelectFields($scheme);

        $aliasQuery = $aliasRepo->createQueryBuilder('al')
            ->select($selectedAliasFields)
            ->join('al.skill', 's')
            ->leftJoin('s.translations', 'st')
            ->leftJoin('st.language', 'l', 'WITH', 'l.code = :locale')
            ->where('(LOWER(al.alias) LIKE :search OR LOWER(st.slug) LIKE :slugSearch)')
            ->setParameter('search', $searchTerm)
            ->setParameter('slugSearch', $slugTerm)
            ->setParameter('locale', $locale);

        // Exclude skills already found in the first step
        if (!empty($foundSkillIds)) {
            $aliasQuery->andWhere('s.id NOT IN (:excludedIds)')
                    ->setParameter('excludedIds', $foundSkillIds);
        }

        $aliases = $aliasQuery->setMaxResults($remainingLimit)
            ->getQuery()
            ->getArrayResult();

        return array_merge($translations, $aliases);
    }
    

    /**
     * Builds the SELECT clause for SkillTranslationEntity
     */
    private function buildTranslationSelectFields(array $scheme): string
    {
        $allowedFields = [
            'id'   => 's.id',    // 💡 Point systématiquement sur l'id du SKILL
            'name' => 'st.name',
            'slug' => 'st.slug',
        ];

        $selects = [];
        foreach ($scheme as $key => $value) {
            if (isset($allowedFields[$key])) {
                $selects[] = $allowedFields[$key] . ' AS ' . $key;
            }
        }

        return !empty($selects) ? implode(', ', $selects) : 's.id AS id, st.name AS name, st.slug AS slug';
    }

    /**
     * Builds the SELECT clause for SkillAliasEntity
     */
    private function buildAliasSelectFields(array $scheme): string
    {
        $allowedFields = [
            'id'    => 's.id',    // 💡 Point systématiquement sur l'id du SKILL
            'alias' => 'al.alias',
            'name'  => 'st.name',
            'slug'  => 'st.slug',
        ];

        $selects = [];
        foreach ($scheme as $key => $value) {
            if (isset($allowedFields[$key])) {
                $selects[] = $allowedFields[$key] . ' AS ' . $key;
            }
        }

        return !empty($selects) ? implode(', ', $selects) : 's.id AS id, al.alias AS alias, st.name AS name';
    }
}