<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: "JobCategory")]
class JobCategoryEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: false)]
    private string $name;
    

    public function getId(): ?int { return $this->id; }

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }
}
