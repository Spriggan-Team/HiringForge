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
     * @param array<string, mixed> $scheme Defines the expected output structure/fields. Named constructor for the initial creation of a Skill
     * @return array<int, array<string, mixed>>
     */
    public function fetchAssociativeArray(string $text, string $locale, array $scheme): array
    {
        $text = mb_strtolower(trim($text));
        $locale = mb_strtolower(trim($locale));
        if ($text === '') {
            return [];
        }

        $maxResults = 15;
        $searchTerm = '%' . $text . '%';

        // 1. Priority Search in Translations
        $transRepo = $this->em->getRepository(SkillTranslationEntity::class);
        $selectedTranslationFields = $this->buildTranslationSelectFields($scheme);

        $translations = $transRepo->createQueryBuilder('st')
            ->select($selectedTranslationFields)
            ->join('st.skill', 's') 
            ->join('st.language', 'l')
            ->where('LOWER(st.name) LIKE :search OR LOWER(st.slug) LIKE :search')
            ->andWhere('l.code = :locale')
            ->setParameter('search', $searchTerm)
            ->setParameter('locale', $locale)
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getArrayResult();

        $totalFound = count($translations);

        if ($totalFound >= $maxResults) {
            return $translations;
        }

        // 2. Additional Search in Aliases
        $remainingLimit = $maxResults - $totalFound;

        $aliasRepo = $this->em->getRepository(SkillAliasEntity::class);
        $selectedAliasFields = $this->buildAliasSelectFields($scheme);

        $aliases = $aliasRepo->createQueryBuilder('al')
            ->select($selectedAliasFields)
            ->join('al.skill', 's')
            ->leftJoin('s.translations', 'st')
            ->leftJoin('st.language', 'l')
            ->where('LOWER(al.alias) LIKE :search')
            ->andWhere('l.code = :locale')
            ->setParameter('search', $searchTerm)
            ->setParameter('locale', $locale)
            ->setMaxResults($remainingLimit)
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