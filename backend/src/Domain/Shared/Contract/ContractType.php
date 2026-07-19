<?php

namespace App\Domain\Shared\Contract;

class ContractType{
    private ?int $id = null;
    private string $organizationId;

    private string $label; // ex: Informatiques, ..ect
    private bool $isDefault = false;

    private ?string $country= null;

    public function __construct(
        string $label,
        bool $isDefault,
        string $organizationId,
        ?int $id = null,
        ?string $country = null,
    ){
        $this->id = $id;
        $this->label = $label;
        $this->isDefault = $isDefault;

        $this->country = $country;
        $this->organizationId = $organizationId;
    }

    public static function reconstitute(
        int $id,
        string $label,
        bool $isDefault,
        string $organizationId,
        ?string $country =null
    )
    {
        return new self(
            id: $id,
            label: $label,
            isDefault: $isDefault,
            country: $country,
            organizationId: $organizationId
        );
    }

    public function id(){
        return $this->id;
    }

    public function label(){
        return $this->label;
    }

    public function isDefault(){
        return $this->isDefault;
    }

    public function country(){
        return $this->country;
    }
    
    public function organizationId(){
        return $this->organizationId;
    }
}