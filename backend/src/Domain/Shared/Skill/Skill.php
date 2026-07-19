<?php

namespace App\Domain\Shared\Skill;
use DomainException;

class Skill
{


    public function __construct(
        public string $label,
        public ?int $id = null,
    ){}

    public function id(){
        return $this->id;
    }

    public function value(): string
    {
        return $this->label;
    }

}