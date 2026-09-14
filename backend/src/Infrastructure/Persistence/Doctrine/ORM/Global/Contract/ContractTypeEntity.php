<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Contract;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: "contract_type")]
class ContractTypeEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 120)]
    private string $label;

    #[ORM\Column(type: "string", length: 2, nullable: true)]
    private ?string $country = null;

    //-- provided by application
    #[ORM\Column(name: "is_default" , type: "boolean")]
    private bool $default = false;

    //-- organization that ave created this type
    #[ORM\Column(type: "string", length: 36, nullable: true)]
    private ?string $organizationId = null;

    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;
        return $this;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function setDefault(bool $default): self
    {
        $this->default = $default;
        return $this;
    }

    public function getOrganizationId(): ?string
    {
        return $this->organizationId;
    }

    public function setOrganizationId(?string $organizationId): self
    {
        $this->organizationId = $organizationId;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id'             => $this->id,
            'label'          => $this->label,
            'country'        => $this->country,
            'default'        => $this->default,
            'organizationId' => $this->organizationId,
        ];
    }
}