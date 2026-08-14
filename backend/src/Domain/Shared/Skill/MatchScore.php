<?php


namespace App\Domain\Shared\Skill;

final class MatchScore
{
    public function __construct(
        public float $score
    ){}
}