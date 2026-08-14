<?php

namespace App\Infrastructure\Services\Skills;

use App\Domain\Shared\Skill\SkillMatch;
use App\Domain\Shared\Skill\SkillRepositoryInterface;
use App\Domain\Shared\Skill\SkillMatcherServiceInterface;
use App\Domain\Shared\Skill\SkillMatchMethod;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\Language\LanguageEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillAliasEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillTranslationEntity;

use Ramsey\Uuid\Uuid;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Repositories\JobOfferQueryRepository;
use Doctrine\ORM\EntityManagerInterface;


use Override;


class SkillMatcherService  implements SkillMatcherServiceInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private readonly SkillNormalizer $normalizer,
        private readonly SkillBatchCache $batchCache,
        private readonly SkillVectorMatcher $vectorMatcher,
        private readonly SkillRepositoryInterface $skillRepository,
    ) {}


    #[Override]
    public function findMatching(
        string $text,
        float $threshold = 0.8,
        string $locale = 'fr',
    ): ?SkillMatch {
        $text = trim($text);

        if ($text === '') {
            return null;
        }

        // STEP 0 — Normalize
        $normalizedText = $this->normalizer->normalize(
            $text,
            $locale,
        );

        if ($normalizedText === '') {
            return null;
        }

        // STEP 1 — Exact / alias
        $resolution = $this->skillRepository->resolveSkill(
            text: $normalizedText,
            locale: $locale,
        );

        if ($resolution !== null) {
            return new SkillMatch(
                skillId: $resolution->skillId,
                method: $resolution->method,
            );
        }

        // STEP 2 — Vector search
        $match = $this->vectorMatcher->searchClosestSkill(
            text: $normalizedText,
            threshold: $threshold,
        );

        if ($match === null) {
            return null;
        }

        return new SkillMatch(
            skillId: $match['skill_id'],
            method: SkillMatchMethod::VECTOR,
            vectorScore: $match['score'],
        );
    }
    

    /**
     * Find or create skill entity
     */
    public function findOrCreateSkill(
        string $name,
        string $canonicalName,
        string $locale = "en",
        ?string $escoUri = null,
        ?string $onetCode = null,
        ?string $skillKey = null,
        bool $shouldFlush = true,
        bool $shouldIndex = true,
        bool $iaValidation = true,
        bool $enableVectorSearch = false,
        bool $allowAutoBatchProcessing = true,
    ): array {
        $language = $this->batchCache->getLanguage($locale);
        $effectiveKey = $skillKey ?? $escoUri ?? $onetCode ?? $this->normalizer->normalize($canonicalName, $locale);


        // STEP 0: Cache for the current batch
        if ($existingSkill = $this->batchCache->getPendingSkill($effectiveKey)) {
            $this->ensureTranslationExists($existingSkill, $name, $locale);
            return [$existingSkill, null];
        }

        // STEP 1: Match the Exact Codes
        if ($skill = $this->findByCodes($escoUri, $onetCode)) {
            return [$skill, null];
        }

        // STEP 2: Match on Slug
        $slug = $this->normalizer->normalize($name, $locale);
        $trans = $this->em->getRepository(SkillTranslationEntity::class)->findOneBy(['slug' => $slug]);

        if ($trans) {
            $skill = $trans->getSkill();
            $this->enrichSkill($skill, $escoUri, $onetCode);
            $this->ensureTranslationExists($skill, $name, $locale);

            if (mb_strtolower($trans->getName()) !== mb_strtolower($name)) {
                $this->ensureAliasExists($skill, $name);
            }

            return [$skill, null];
        }

        // STEP 3: Match on Alias
        if ($skill = $this->findByAlias($name, $slug)) {
            $this->enrichSkill($skill, $escoUri, $onetCode);
            return [$skill, null];
        }


        // STEP 4: Vector Search
        $vector = null;
        if ($enableVectorSearch) {
            $vector = $this->vectorMatcher->generateEmbedding($name);
            if (!empty($vector)) {
                $candidateSkill = $this->vectorMatcher->findMatchingConcept($name, $vector, $iaValidation);
                if ($candidateSkill) {
                    $this->enrichSkill($candidateSkill, $escoUri, $onetCode);
                    $this->ensureTranslationExists($candidateSkill, $name, $locale);

                    if ($shouldIndex) {
                        $this->vectorMatcher->indexSkill($candidateSkill->getId(), $candidateSkill->getCanonicalName(), $vector);
                    }

                    return [$candidateSkill, $vector];
                }
            }
        }

        // STEP 5: Création (Si aucun match)
        $skill = new SkillEntity(
            id: Uuid::uuid4()->toString(),
            canonicalName: $canonicalName
        );

        if ($escoUri) { $skill->setEscoUri($escoUri); }
        if ($onetCode) { $skill->setOnetCode($onetCode); }

        $this->createTranslationAndPersist(
            name: $name,
            slug: $slug,
            skill: $skill,
            language: $language
        );

        $this->em->persist($skill);

        if ($allowAutoBatchProcessing) {
            $this->batchCache->setPendingSkill($effectiveKey, $skill);
        }

        if ($shouldFlush) {
            $this->em->flush();
            $this->batchCache->clear();
        }

        //-- Generate only if vector search if not activated (cause already handled upward)
        if (empty($vector)) {
            $vector = $this->vectorMatcher->generateEmbedding($name);
        }

        if (!empty($vector) && $shouldIndex) {
            $this->vectorMatcher->indexSkill($skill->getId(), $canonicalName, $vector);
        }

        return [$skill, $vector];
    }



    //----------------------------
    //---- Helpers
    //----------------------------

    /**
     * Find a skill with their identifiant escoUri & onetCode
     */
    private function findByCodes(?string $escoUri, ?string $onetCode): ?SkillEntity
    {
        $repo = $this->em->getRepository(SkillEntity::class);
        if ($escoUri && ($skill = $repo->findOneBy(['escoUri' => $escoUri]))) {
            return $this->enrichSkill($skill, $escoUri, $onetCode);
        }
        if ($onetCode && ($skill = $repo->findOneBy(['onetCode' => $onetCode]))) {
            return $this->enrichSkill($skill, $escoUri, $onetCode);
        }
        return null;
    }


    private function findByAlias(string $name, string $slug): ?SkillEntity
    {
        $alias = $this->em->getRepository(SkillAliasEntity::class)->createQueryBuilder('a')
            ->where('LOWER(a.alias) = :alias OR LOWER(a.alias) = :rawName')
            ->setParameter('alias', str_replace('-', ' ', $slug))
            ->setParameter('rawName', mb_strtolower(trim($name)))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $alias?->getSkill();
    }



    private function ensureAliasExists(SkillEntity $skill, string $aliasName): void
    {
        $exists = $this->em->getRepository(SkillAliasEntity::class)->createQueryBuilder('a')
            ->where('a.skill = :skill')
            ->andWhere('LOWER(a.alias) = :alias')
            ->setParameter('skill', $skill)
            ->setParameter('alias', mb_strtolower(trim($aliasName)))
            ->getQuery()
            ->getOneOrNullResult();

        if (!$exists) {
            $this->em->persist(new SkillAliasEntity(trim($aliasName), $skill));
        }
    }


    private function ensureTranslationExists(SkillEntity $skill, string $name, string $locale): void
    {
        $language = $this->batchCache->getLanguage($locale);
        $slug = $this->normalizer->normalize($name, $locale);

        $existing = $this->em->getRepository(SkillTranslationEntity::class)->findOneBy([
            'skill' => $skill,
            'language' => $language,
        ]);

        if (!$existing) {
            $this->createTranslationAndPersist($name, $slug, $skill, $language);
        }
    }

    
    private function enrichSkill(SkillEntity $skill, ?string $escoUri, ?string $onetCode): SkillEntity
    {
        if ($escoUri && !$skill->getEscoUri()) { $skill->setEscoUri($escoUri); }
        if ($onetCode && !$skill->getOnetCode()) { $skill->setOnetCode($onetCode); }
        return $skill;
    }


    private function createTranslationAndPersist(string $name, string $slug, SkillEntity $skill, LanguageEntity $language): SkillTranslationEntity
    {
        $trans = new SkillTranslationEntity(
            name: $name, 
            slug: $slug, 
            skill: $skill, 
            language: $language
        );
        $skill->addTranslation($trans);
        $this->em->persist($trans);
        return $trans;
    }

    public function clearPendingBatchCache(){
        $this->batchCache->clear();
    }
}