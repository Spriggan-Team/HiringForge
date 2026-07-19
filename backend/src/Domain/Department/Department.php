<?php

namespace App\Domain\Department;

class Department{
    private ?int $id = null;

    private string $label;

    public function __construct(
        string $label,
        ?int $id = null,
    )
    {
        $this->id = $id;
        $this->label = $label;
    }

    public function getId(){
        return $this->id;
    }

    public function getLabel(){
        return $this->label;
    }
}
