<?php

namespace App\Domain\Shared\Skill;

interface SkillMatcherServiceInterface
{
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
    ): array ;
}