<?php 

namespace App\Domain\Shared\Skill;

final readonly class SkillResolution
{
    public function __construct(
        public string $skillId,
        public SkillMatchMethod $method,
    ) {}
}