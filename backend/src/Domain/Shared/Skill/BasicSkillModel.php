<?php

namespace App\Domain\Shared\Skill;


final readonly class BasicSkillModel
{
    public function __construct(
        private string $id,
        private string $name,
    ) {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }
}