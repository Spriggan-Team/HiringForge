<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Category;

use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobCategoryEntity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


#[ORM\Entity]
#[ORM\Table(name: "category")]
class CategoryEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $label;

    #[ORM\OneToMany(
        mappedBy: 'category',
        targetEntity: JobCategoryEntity::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    private Collection $jobCategories;

    public function __construct()
    {
        $this->jobCategories = new ArrayCollection();
    }

    //======================
    //   GETTERS
    //=====================

    public function getId(): ?int { return $this->id; }
    public function getLabel(): string { return $this->label; }
    public function getJobCategories(): Collection { return $this->jobCategories; }

    //======================
    //   SETTERS
    //=====================

    public function setLabel(string $label): static
    {
        $this->label = $label;
        return $this;
    }

}
