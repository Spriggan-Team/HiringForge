<?php

namespace App\Domain\Shared\Service;

interface AiValidatorServiceInterface
{
    public function isSameSkillConcept(string $name, string $canonicalName): bool;
}