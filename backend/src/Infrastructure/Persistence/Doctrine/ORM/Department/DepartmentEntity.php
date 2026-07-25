<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Department;

use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Department\Repositories\DepartmentRespository;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JsonSerializable;

#[ORM\Table(name: 'departments')]
#[ORM\Entity(repositoryClass: DepartmentRespository::class)]
class DepartmentEntity implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $label;

    #[ORM\Column(length: 425, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $externalRef = null;

    // --- RELATIONS ---

    // Scope per Entreprise / Organisation
    #[ORM\ManyToOne(targetEntity: CompanyEntity::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?CompanyEntity $company = null;

    // hierarchy : Parent (ex: "Engineering")
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?self $parent = null;

    // hierarchy : children (ex: "Backend", "Frontend")
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $children;

    // Associated Offers
    #[ORM\OneToMany(mappedBy: 'department', targetEntity: JobOfferEntity::class)]
    private Collection $jobOffers;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->jobOffers = new ArrayCollection();
    }

    public static function create(
        string $label,
        ?string $code = null,
        ?string $description =null,
        bool $isActive =true,
        ?string $externalRef = null,
        ?CompanyEntity $company = null,
    ): self{
        $entity = new self();
        $entity->setLabel($label)
               ->setCode($code)
               ->setDescription($description)
               ->setIsActive($isActive)
               ->setExternalRef($externalRef)
               ->setCompany($company);

        return $entity;
    }

    // --- GETTERS / SETTERS ---

    public function getId(): ?int { return $this->id; }

    public function getLabel(): string { return $this->label; }
    public function setLabel(string $label): static { $this->label = $label; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getCode(): ?string { return $this->code; }
    public function setCode(?string $code): static { $this->code = $code; return $this; }

    public function isActive(): bool { return $this->isActive; }
    public function setIsActive(bool $isActive): static { $this->isActive = $isActive; return $this; }

    public function getExternalRef(): ?string { return $this->externalRef; }
    public function setExternalRef(?string $externalRef): static { $this->externalRef = $externalRef; return $this; }

    public function getCompany(): ?CompanyEntity { return $this->company; }
    public function setCompany(?CompanyEntity $company): static { $this->company = $company; return $this; }

    public function getParent(): ?self { return $this->parent; }
    public function setParent(?self $parent): static { $this->parent = $parent; return $this; }

    public function getChildren(): Collection { return $this->children; }

    public function getJobOffers(): Collection { return $this->jobOffers; }

    // ---  JSON SERIALISATION---

    public function jsonSerialize(): array
    {
        return [
            'id'           => $this->id,
            'label'        => $this->label,
            'description'  => $this->description,
            'code'         => $this->code,
            'isActive'     => $this->isActive,
            'externalRef'  => $this->externalRef,
            'companyId'    => $this->company?->getId(),
            'parentId'     => $this->parent?->getId(),
            'children'     => $this->children->toArray(), // Charge les sous-départements récursivement
        ];
    }
}