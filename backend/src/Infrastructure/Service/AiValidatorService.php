<?php

namespace App\Infrastructure\Service;

use App\Domain\Shared\Service\AiValidatorServiceInterface;
use Override;

class AiValidatorService implements AiValidatorServiceInterface
{
    #[Override]
    public function isSameSkillConcept(string $name, string $canonicalName): bool
    {
        throw new \Exception('Not implemented');
    }
}