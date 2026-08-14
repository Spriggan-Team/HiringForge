<?php

namespace App\Domain\Shared\Skill;


enum SkillMatchMethod: string
{
    case EXACT = 'exact';
    case ALIAS = 'alias';
    case VECTOR = 'vector';

    public function score(float $vectorScore = 0.0): float
    {
        return match ($this) {
            self::EXACT => 1.0,
            self::ALIAS => 0.95,
            self::VECTOR => $vectorScore,
        };
    }
}