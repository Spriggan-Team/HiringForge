<?php

namespace App\Domain\Department;

class Department{
    private ?int $id = null;
    private ?Department $parent = null;

    private string $label;
    private ?string $code = null;
    private bool $isActive = true;
    private ?string $description= null;
    private ?string $externalRef = null;
    private ?string $companyId = null;


    public function __construct(
        string $label,
        ?int $id = null,
        ?Department $parent =null,
        ?string $companyId =null,
        ?string $code = null,
        bool $isActive = true,
        ?string $description =null,
        ?string $externalRef = null
    )
    {
        $this->id = $id;
        $this->code = $code;
        $this->label = $label;
        $this->parent = $parent;
        $this->isActive = $isActive;
        $this->companyId = $companyId;
        $this->description = $description;
        $this->externalRef = $externalRef;
    }

    //-- create (hold domain logic at initialization)
    public  static function create(
        string $label,
        ?int $id = null,
        ?Department $parent =null,
        ?string $companyId =null,
        ?string $code = null,
        bool $isActive = true,
        ?string $description =null,
        ?string $externalRef = null
    ){
        return new self(
            id: $id,
            parent: $parent,
            code: $code,
            label: $label,
            isActive: $isActive,
            companyId: $companyId,
            externalRef: $externalRef,
            description: $description,
        );
    }


    //--Warning (absolutely no guard or domain rules here)
    public static function reconstitue(
        int $id,
        ?Department $parent,
        ?string $companyId,
        ?string $code,
        string $label,
        bool $isActive,
        ?string $description,
        ?string $externalRef
    ): self{
        return new self(
            id: $id,
            code: $code,
            label: $label,
            parent: $parent,
            isActive: $isActive,
            companyId: $companyId,
            externalRef: $externalRef,
            description: $description,
        );
    }

    //-------------------
    //-- GETTERS
    //-------------------

    public function id(){
        return $this->id;
    }

    public function parent(){
        return $this->parent;
    }

    public function getParentIds(): array
    {
        if ($this->parent === null) {
            return [];
        }

        return [
            ...$this->parent->getParentIds(),
            $this->parent->id,
        ];
    }

    public function companyId(){
        return $this->companyId;
    }

    public function label(){
        return $this->label;
    }

    public function code(){
        return $this->code;
    }

    public function isActive(){
        return $this->isActive;
    }

    public function externalRef(){
        return $this->externalRef;
    }

    public function description(){
        return $this->description;
    }
  
    //-----------------
    //-- SETTERS
    //-----------------


    public function setParent(?Department $parent){
        $this->parent = $parent;
        return $this;
    }

    public function setIsActive(bool $isActive){
        $this->isActive = $isActive;
        return $this;
    }

    //-------------------
    //-- FORMAT
    //-------------------

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'label' => $this->label,
            'description' => $this->description,
            'parentIds' => $this->getParentIds(),
        ];
    }
}
