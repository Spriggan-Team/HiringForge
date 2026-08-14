<?php


namespace App\Domain\Shared\Skill;


final readonly class RequiredSkill
{
    public function __construct(
        public string $skillId,
        public float $weight = 1.0,
    ) {}
}