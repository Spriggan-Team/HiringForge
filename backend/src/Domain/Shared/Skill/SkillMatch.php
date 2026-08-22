<?php


namespace App\Domain\Shared\Skill;


final readonly class SkillMatch
{
    public function __construct(
        public string $skillId,
        public SkillMatchMethod $method,
        public ?float $vectorScore = null,
    ) {}

    public function confidence(): float
    {
        return match ($this->method) {
            SkillMatchMethod::EXACT => 1.0,
            SkillMatchMethod::ALIAS => 0.95,
            SkillMatchMethod::VECTOR => $this->vectorScore ?? 0.0,
        };
    }
}