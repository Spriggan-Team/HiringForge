<?php

namespace App\Infrastructure\Persistence\Service;


use App\Domain\Shared\Service\EmbeddingProviderInterface;
use App\Domain\Shared\Service\AiValidatorServiceInterface;
use App\Domain\Shared\Language\LanguageRepositoryInterface;
use App\Domain\Shared\Service\VectorServiceInterface;


use App\Infrastructure\Persistence\Doctrine\ORM\Global\Language\LanguageEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillAliasEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillTranslationEntity;


use Ramsey\Uuid\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;


class SkillMatcherService  
{
    // Banned words filtered during slug normalization
    private const STOPWORDS = [
        'fr' => ['de', 'du', 'la', 'le', 'des', 'les', 'en', 'un', 'une', 'et', 'a', 'pour', 'par'],
        'en' => ['of', 'the', 'and', 'in', 'for', 'a', 'an', 'to', 'with', 'by', 'on', 'at'],
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private SluggerInterface $slugger,
        private LanguageRepositoryInterface $languageRepository,
        private ?VectorServiceInterface $vectorService = null,
        private ?EmbeddingProviderInterface $embeddingProvider = null,
        private ?AiValidatorServiceInterface $aiValidator = null // Optional AI service for validation robustness
    ) {}


    /**
     * Cleans and standardizes a string (lowercase, without stopwords, slugged)
     * @throws \Exception
     */
    public function normalize(string $text, string $locale = 'fr'): string
    {
        $cleanText = mb_strtolower(trim($text));

        // Filter stopwords based on locale
        $stopwords = self::STOPWORDS[$locale] ?? [];
        if (!empty($stopwords)) {
            $words = preg_split('/\s+/u', $cleanText);
            $filteredWords = array_filter($words, fn($w) => !in_array($w, $stopwords, true));
            $cleanText = implode(' ', $filteredWords);
        }

        return $this->slugger->slug($cleanText)->toString();
    }

    /**
     * Finds an existing skill or creates a new one following the decision tree.
     *
     * @return array{0: SkillEntity, 1?: array<int, float>}
     */
    public function findOrCreateSkill(
        string $name,
        string $locale,
        string $canonicalName,
        ?string $escoUri = null,
        ?string $onetCode = null,
        bool $shouldFlush = true,
        bool $shouldIndex = true,
        bool $enableVectorSearch = false
    ): array {
        $skillRepo = $this->em->getRepository(SkillEntity::class);
        $transRepo = $this->em->getRepository(SkillTranslationEntity::class);
        $aliasRepo = $this->em->getRepository(SkillAliasEntity::class);

        // ==========================================
        // STEP 1: Exact Match by External Code
        // ==========================================
        if ($escoUri) {
            $existing = $skillRepo->findOneBy(['escoUri' => $escoUri]);
            if ($existing) {
                return [$this->enrichSkill($existing, null, $onetCode), null];
            }
        }

        if ($onetCode) {
            $existing = $skillRepo->findOneBy(['onetCode' => $onetCode]);
            if ($existing) {
                return [$this->enrichSkill($existing, $escoUri, null), null];
            }
        }

        // Retrieve language configuration
        $language = $this->em->getRepository(LanguageEntity::class)->findOneBy(['code' => $locale]);
        if (!$language) {
            throw new \Exception("The language '$locale' was not found in the database.");
        }

        // ==========================================
        // STEP 2: Exact Match on Slug
        // ==========================================
        $slug = $this->normalize($name, $locale);

        $translation = $transRepo->findOneBy([
            'language' => $language,
            'slug'     => $slug,
        ]);

        if ($translation) {
            return [$this->enrichSkill($translation->getSkill(), $escoUri, $onetCode), null];
        }

        // ==========================================
        // STEP 3: Match on Aliases / Synonyms
        // ==========================================
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

        // ==========================================
        // STEP 4: Embeddings / Vector Search
        // ==========================================
        if ($enableVectorSearch && $this->embeddingProvider) {
            $candidateSkill = $this->findSkillByVectorSearch($name, 0.88);

            if ($candidateSkill) {
                $isValidMatch = true;
                if ($this->aiValidator) {
                    $isValidMatch = $this->aiValidator->isSameSkillConcept($name, $candidateSkill->getCanonicalName());
                }

                if ($isValidMatch) {
                    return [$this->enrichSkill($candidateSkill, $escoUri, $onetCode), null];
                }
            }
        }

        // ==========================================
        // STEP 5: Fallback: Create New Skill Entity
        // ==========================================
        // Grâce aux UUIDs, l'ID est généré tout de suite !
        $skill = new SkillEntity(
            id: Uuid::uuid4()->toString(),
            canonicalName: $canonicalName
        );

        if ($escoUri) {
            $skill->setEscoUri($escoUri);
        }
        if ($onetCode) {
            $skill->setOnetCode($onetCode);
        }

        $translation = new SkillTranslationEntity(
            name: $name,
            slug: $slug,
            skill: $skill,
            language: $language
        );

        $this->em->persist($skill);
        $this->em->persist($translation);

        if ($shouldFlush) {
            $this->em->flush();
        }

        $vector = null;
        // Génération du vecteur si le provider est présent
        if ($this->embeddingProvider) {
            $vector = $this->embeddingProvider->generateEmbedding($name);
            
            if (!empty($vector)) {
                if (method_exists($skill, 'setEmbedding')) {
                    $skill->setEmbedding($vector);
                }

                if ($shouldIndex && $this->vectorService) {
                    // Indexation directe dans Qdrant
                    $this->vectorService->indexSkill(
                        skillId: $skill->getId(),
                        skillName: $canonicalName,
                        vector: $vector
                    );
                }
            }
        }

        return [$skill, $vector];
    }


    /**
     * Executes vector search against the database (compatible with pgvector extension)
     */
    private function findSkillByVectorSearch(string $text, float $threshold = 0.88): ?SkillEntity
    {
        if (!$this->vectorService) {
            return null;
        }

        // 1. Query external vector database to retrieve target MySQL ID
        $skillId = $this->vectorService->searchClosestSkillId($text, $threshold);

        if (!$skillId) {
            return null;
        }

        // 2. Fetch the corresponding skill directly from MariaDB by primary key
        return $this->em->getRepository(SkillEntity::class)->find($skillId);
    }


    /**
     * Enriches an existing skill with missing external source codes
     */
    private function enrichSkill(SkillEntity $skill, ?string $onetCode = null, ?string $escoUri = null): SkillEntity
    {
        if ($onetCode && !$skill->getOnetCode()) {
            $skill->setOnetCode($onetCode);
        }
        if ($escoUri && !$skill->getEscoUri()) {
            $skill->setEscoUri($escoUri);
        }
        return $skill;
    }

    /**
     * Calculates Cosine Similarity between two PHP vectors
     */
    public function cosineSimilarity(array $vecA, array $vecB): float
    {
        $dotProduct = 0.0; 
        $normA = 0.0;  
        $normB = 0.0;

        foreach ($vecA as $i => $val) {
            $dotProduct += $val * $vecB[$i];
            $normA += $val * $val;
            $normB += $vecB[$i] * $vecB[$i];
        }

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}