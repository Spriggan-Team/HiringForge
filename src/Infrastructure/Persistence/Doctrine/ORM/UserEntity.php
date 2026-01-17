<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;


#[ORM\Table(name: "user")]
#[ORM\Entity]
class UserEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "guid", unique: true)]
    private string $id;

    #[ORM\Column(length: 150)]
    private string $name;

    #[ORM\Column(length: 255, unique: true, nullable: false)]
    private string $email;

    #[ORM\Column(nullable: false)]
    private string $imagePath;

    #[ORM\Column(length: 255, nullable: false)]
    private string $siret;

    #[ORM\Column(length: 255, nullable: false)]
    private string $password;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: JobOfferEntity::class )]
    private Collection $jobOffers;


    public function __construct()
    {
        $this->jobOffers = new ArrayCollection();
    }

    public static function reconstitue(
        string $id,
        string $name,
        string $email,
        string $password,
        string $siret,
        string $imagePath,
    ): self {
        $entity = new self();
        $entity->$id;
        $entity->name     = $name;
        $entity->email    = $email;
        $entity->password = $password;
        $entity->siret    = $siret;
        $entity->imagePath = $imagePath;
        return $entity;
    }

    /* =======================
     * GETTERS
     * ======================= */

    public function getId(): string { return $this->id; }

    public function getName(): string { return $this->name; }

    public function getEmail():string { return $this->email; }

    public function getPassword():string { return $this->password; }

    public function getSiret(){ return $this->siret; }

    public function getImagePath(){ return $this->imagePath; }
    
    public function getJobOffer(): Collection { return $this->jobOffers; }

    /* =======================
     * SETTERS
     * ======================= */

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }
    
    public function setName(string $name): static { 
        $this->name = $name;
        return $this;    
    }

    public function setEmail(string $email): static{ 
        $this->email = $email;
        return $this;
    }

    public function setPassword(string $hash): static { 
        $this->password = $hash;
        return $this;
    }

    public function setSiret(string $siret): static{
        $this->siret = $siret;
        return $this;    
    }

    public function setImagePath(string $path): static
    {
        $this->imagePath = $path;
        return $this;
    }

}
