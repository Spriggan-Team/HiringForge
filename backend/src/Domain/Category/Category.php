<?php

namespace App\Domain\Category;

class Category
{
    public function __construct(
        public string $label,
        public ?int $id = null,
    ){}

    public static function reconstitute(
        string $label,
        ?int $id = null,
    ){
        return new self(
            id: $id,
            label: $label
        );
    }

    public function id(){
        return $this->id;
    }

    public function label(){
        return $this->label;
    }
}