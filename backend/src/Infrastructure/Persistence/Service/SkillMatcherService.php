<?php

namespace App\Infrastructure\Persistence\Service;

use App\Domain\Shared\Service\EmbeddingProviderInterface;
use App\Domain\Shared\Service\AiValidatorServiceInterface;
use App\Domain\Shared\Language\LanguageRepositoryInterface;
use App\Domain\Shared\Service\VectorServiceInterface;
use App\Domain\Shared\Skill\SkillMatcherServiceInterface;


use App\Infrastructure\Persistence\Doctrine\ORM\Global\Language\LanguageEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillAliasEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillTranslationEntity;

use Ramsey\Uuid\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;


class SkillMatcherService  implements SkillMatcherServiceInterface
{
    private const STOPWORDS = [
        'fr' => ['de', 'du', 'la', 'le', 'des', 'les', 'en', 'un', 'une', 'et', 'a', 'pour', 'par'],
        'en' => ['of', 'the', 'and', 'in', 'for', 'a', 'an', 'to', 'with', 'by', 'on', 'at'],
    ];

    /** @var array<string, SkillEntity> */
    private array $pendingBatchSkills = [];
    private array $languageCache = [];

    public function __construct(
        private EntityManagerInterface $em,
        private SluggerInterface $slugger,
        private LanguageRepositoryInterface $languageRepository,
        private ?VectorServiceInterface $vectorService = null,
        private ?EmbeddingProviderInterface $embeddingProvider = null,
        private ?AiValidatorServiceInterface $aiValidator = null 
    ) {}

    public function normalize(string $text, string $locale = 'fr'): string
    {
        $cleanText = mb_strtolower(trim($text));
        $stopwords = self::STOPWORDS[$locale] ?? [];
        if (!empty($stopwords)) {
            $words = preg_split('/\s+/u', $cleanText);
            $filteredWords = array_filter($words, fn($w) => !in_array($w, $stopwords, true));
            $cleanText = implode(' ', $filteredWords);
        }

        return $this->slugger->slug($cleanText)->toString();
    }

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
        $skillRepo = $this->em->getRepository(SkillEntity::class);
        $transRepo = $this->em->getRepository(SkillTranslationEntity::class);
        $aliasRepo = $this->em->getRepository(SkillAliasEntity::class);

        $effectiveKey = $skillKey ?? $escoUri ?? $onetCode ?? $this->normalize($canonicalName, $locale);

        // Language cache to avoid N+1 SELECT queries
        if (!isset($this->languageCache[$locale])) {
            $language = $this->em->getRepository(LanguageEntity::class)->findOneBy(['code' => $locale]);
            if (!$language) {
                throw new \Exception("The language '$locale' was not found in the database.");
            }
            $this->languageCache[$locale] = $language;
        }
        $language = $this->languageCache[$locale];

        // STEP 0: Cache for the current batch
        if (isset($this->pendingBatchSkills[$effectiveKey])) {
            $existingSkill = $this->pendingBatchSkills[$effectiveKey];
            $this->ensureTranslationExists($existingSkill, $name, $locale);
            return [$existingSkill, null];
        }

        // STEP 1: Match the Exact Codes
        if ($escoUri) {
            $existing = $skillRepo->findOneBy(['escoUri' => $escoUri]);
            if ($existing) {
                return [$this->enrichSkill($existing, $escoUri, $onetCode), null];
            }
        }

        if ($onetCode) {
            $existing = $skillRepo->findOneBy(['onetCode' => $onetCode]);
            if ($existing) {
                return [$this->enrichSkill($existing, $escoUri, $onetCode), null];
            }
        }

        // STEP 2: Match on Slug
        $slug = $this->normalize($name, $locale);
        $translation = $transRepo->findOneBy([
            'slug' => $slug,
        ]);

        if ($translation) {
            $skill = $translation->getSkill();
            $this->enrichSkill($skill, $escoUri, $onetCode);
            $this->ensureTranslationExists($skill, $name, $locale);
            
            if (mb_strtolower($translation->getName()) !== mb_strtolower($name)) {
                $this->ensureAliasExists($skill, $name);
            }

            return [$skill, null];
        }

        if ($translation) {
            return [$this->enrichSkill($translation->getSkill(), $escoUri, $onetCode), null];
        }

        // STEP 3: Match on Alias
        $existingAlias = $aliasRepo->createQueryBuilder('a')
            ->where('LOWER(a.alias) = :alias OR LOWER(a.alias) = :rawName')
            ->setParameter('alias', str_replace('-', ' ', $slug))
            ->setParameter('rawName', mb_strtolower(trim($name)))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($existingAlias) {
            return [$this->enrichSkill($existingAlias->getSkill(), $escoUri, $onetCode), null];
        }

        $vector = null;

        // STEP 4: Vector Search
        if ($enableVectorSearch && $this->embeddingProvider) {
            // Embeddings (one time!)
            $vector = $this->embeddingProvider->generateEmbedding($name);

            if (!empty($vector)) {
                //-- serch the calculated vector in Vector DB
                $candidateSkill = $this->findSkillByVectorSearch($vector, 0.88);

                if ($candidateSkill) {
                    $isValidMatch = true;
                    if ($this->aiValidator && $iaValidation) {
                        $isValidMatch = $this->aiValidator->isSameSkillConcept($name, $candidateSkill->getCanonicalName());
                    }

                    if ($isValidMatch) {
                        $skill = $this->enrichSkill($candidateSkill, $escoUri, $onetCode);
                        $this->ensureTranslationExists($skill, $name, $locale);

                        // If skill already exist by is not in Vector BDD we update/insert it
                        if ($shouldIndex && $this->vectorService) {
                            $this->vectorService->indexSkill(
                                skillId: $skill->getId(),
                                skillName: $skill->getCanonicalName(),
                                vector: $vector
                            );
                        }

                        return [$skill, $vector];
                    }
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
            $this->pendingBatchSkills[$effectiveKey] = $skill;
        }

        if ($shouldFlush) {
            $this->em->flush();
            $this->clearPendingBatchCache();
        }

        //-- Generate only if vector search if not activated (cause already handled upward)
        if ($this->embeddingProvider && empty($vector)) {
            $vector = $this->embeddingProvider->generateEmbedding($name);
        }

        if (!empty($vector)) {
            if (method_exists($skill, 'setEmbedding')) {
                $skill->setEmbedding($vector);
            }

            // Indexation  Vector DB 
            if ($shouldIndex && $this->vectorService) {
                $this->vectorService->indexSkill(
                    skillId: $skill->getId(),
                    skillName: $canonicalName,
                    vector: $vector
                );
            }
        }

        return [$skill, $vector];
    }



    public function clearPendingBatchCache(): void
    {
        $this->pendingBatchSkills = [];
        $this->languageCache = [];
    }



    private function ensureAliasExists(SkillEntity $skill, string $aliasName): void
    {
        $aliasRepo = $this->em->getRepository(SkillAliasEntity::class);
        
        $existing = $aliasRepo->createQueryBuilder('a')
            ->where('a.skill = :skill')
            ->andWhere('LOWER(a.alias) = :alias')
            ->setParameter('skill', $skill)
            ->setParameter('alias', mb_strtolower(trim($aliasName)))
            ->getQuery()
            ->getOneOrNullResult();

        if (!$existing) {
            $alias = new SkillAliasEntity(
                alias: trim($aliasName),
                skill: $skill
            );
            $this->em->persist($alias);
        }
    }


    private function ensureTranslationExists(SkillEntity $skill, string $name, string $locale): void
    {
        $language = $this->languageCache[$locale] ?? $this->em->getRepository(LanguageEntity::class)->findOneBy(['code' => $locale]);
        $slug = $this->normalize($name, $locale);

        $transRepo = $this->em->getRepository(SkillTranslationEntity::class);
        $existingTrans = $transRepo->findOneBy([
            'skill' => $skill,
            'language' => $language
        ]);

        if (!$existingTrans) {
            $this->createTranslationAndPersist(
                name: $name,
                slug: $slug,
                skill: $skill,
                language: $language
            );
        }
    }



    private function findSkillByVectorSearch(
        array $vector,
        float $threshold = 0.88,
        float $accept = 0.95,
        ?callable $onAccept = null,
        ?callable $onReject = null,
        ?callable $onCandidate = null
    ): ?SkillEntity {
        if (!$this->vectorService) {
            if ($onReject) { $onReject(null); }
            return null;
        }

        $result = $this->vectorService->searchClosestSkillByVector($vector, $threshold);

        if (!$result) {
            if ($onReject) { $onReject(null); }
            return null;
        }

        $skillId = $result['skill_id'];
        $score   = $result['score'];

        $skill = $this->em->getRepository(SkillEntity::class)->find($skillId);

        if (!$skill) {
            if ($onReject) { $onReject($result); }
            return null;
        }

        if ($score >= $accept) {
            if ($onAccept) { $onAccept($skill, $score); }
            return $skill;
        }

        if ($onCandidate) {
            $isAccepted = $onCandidate($skill, $score);
            if (!$isAccepted) {
                if ($onReject) { $onReject($result); }
                return null;
            }
        }

        return $skill;
    }



    //-- Realignment of parameters ($escoUri followed by $onetCode)
    private function enrichSkill(SkillEntity $skill, ?string $escoUri = null, ?string $onetCode = null): SkillEntity
    {
        if ($escoUri && !$skill->getEscoUri()) {
            $skill->setEscoUri($escoUri);
        }
        if ($onetCode && !$skill->getOnetCode()) {
            $skill->setOnetCode($onetCode);
        }
        return $skill;
    }



    private function createTranslationAndPersist(
        string &$name,
        string &$slug,
        SkillEntity &$skill,
        LanguageEntity &$language,
        bool $shouldPersist = true,
    ): SkillTranslationEntity {
        $translation = new SkillTranslationEntity(
            name: $name,
            slug: $slug,
            skill: $skill,
            language: $language,
        );

        $skill->addTranslation($translation);

        if ($shouldPersist) {
            $this->em->persist($translation);
        }

        return $translation;
    }



}