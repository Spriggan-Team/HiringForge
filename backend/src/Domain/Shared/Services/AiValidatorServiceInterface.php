<?php

namespace App\Domain\Shared\Services;


interface AiValidatorServiceInterface
{
    public function isSameSkillConcept(
        string $name,
        string $canonicalName
    ): bool;

}