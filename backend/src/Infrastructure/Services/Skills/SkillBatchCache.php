<?php

namespace App\Infrastructure\Services\Skills;

use App\Domain\Shared\Language\LanguageRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Language\LanguageEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillEntity;



/**
 * For Runtime Skill Batch/Cache management
 */
class SkillBatchCache
{
    /** @var array<string, SkillEntity> */
    private array $pendingSkills = [];

    /** @var array<string, LanguageEntity> */
    private array $languageCache = [];

    public function __construct(private readonly LanguageRepositoryInterface $languageRepository) {}

    /**
     * Manege language cache to avoid N+1 SELECT queries
     */
    public function getLanguage(string $locale): LanguageEntity
    {
        if (!isset($this->languageCache[$locale])) {
            $language = $this->languageRepository->findByCode($locale);
            if (!$language) {
                throw new \RuntimeException("Language '$locale' not found.");
            }
            $this->languageCache[$locale] = $language;
        }

        return $this->languageCache[$locale];
    }


    public function getPendingSkill(string $key): ?SkillEntity
    {
        return $this->pendingSkills[$key] ?? null;
    }

    public function setPendingSkill(string $key, SkillEntity $skill): void
    {
        $this->pendingSkills[$key] = $skill;
    }

    public function clear(): void
    {
        $this->pendingSkills = [];
        $this->languageCache = [];
    }
}