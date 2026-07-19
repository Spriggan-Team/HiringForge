<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Department;

use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


#[ORM\Entity]
#[ORM\Table(name: "departments")]
class DepartmentEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 120, unique: true)]
    private string $label;

    #[ORM\OneToMany(
        mappedBy: "department",
        targetEntity: JobOfferEntity::class
    )]
    private Collection $jobOffers;

    public function __construct()
    {
        $this->jobOffers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getJobOffers(): Collection
    {
        return $this->jobOffers;
    }
}