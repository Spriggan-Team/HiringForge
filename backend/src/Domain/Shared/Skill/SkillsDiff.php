<?php

namespace App\Domain\Shared\Skill;


use App\Domain\Shared\Skill\BasicSkillModel;

final readonly class SkillsDiff
{
    /**
     * @param BasicSkillModel[] $added
     * @param BasicSkillModel[] $removed
     * @param BasicSkillModel[] $unchanged
     */
    public function __construct(
        public array $added = [],
        public array $removed = [],
        public array $unchanged = [],
    ) {
    }
}