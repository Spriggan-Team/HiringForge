<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


#[ORM\Entity]
#[ORM\Table(name: "category")]
class CategoryEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

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
    public function getName(): string { return $this->name; }
    public function getJobCategories(): Collection { return $this->jobCategories; }

    //======================
    //   SETTERS
    //=====================

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

}
