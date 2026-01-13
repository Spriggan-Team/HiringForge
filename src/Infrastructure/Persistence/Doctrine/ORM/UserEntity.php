<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;


#[ORM\Table(name: "User")]
#[ORM\Entity]
class UserEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "guid", unique: true)]
    public ?string $id = null;

    #[ORM\Column(length: 150)]
    public ?string $name = null;

    #[ORM\Column(length: 255, unique: true, nullable: false)]
    public ?string $email = null;

    #[ORM\Column(length: 255, nullable: false)]
    public ?string $siret = null;

    #[ORM\Column(length: 255, nullable: false)]
    public ?string $password = null;

    #[ORM\OneToMany(mappedBy: "post", targetEntity: JobOfferEntity::class )]
    public Collection $posts;

    public static function create(
        string $id,
        string $name,
        string $email,
        string $password,
        string $siret
    ): self {
        $entity = new self();
        $entity->setId($id);
        $entity->name     = $name;
        $entity->email    = $email;
        $entity->password = $password;
        $entity->siret    = $siret;
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
    

    /* =======================
     * SETTERS
     * ======================= */


    public function setId(string $id): void { $this->id = $id; }
    
    public function setName(string $name): void { $this->name = $name; }

    public function setEmail(string $email): void{ $this->email = $email; }

    public function setPassword(string $hash): void { $this->password = $hash; }

    public function setSiret(string $siret): void{ $this->siret = $siret; }

}
