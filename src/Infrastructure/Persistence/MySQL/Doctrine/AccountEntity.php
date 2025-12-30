<?php

namespace App\Infrastructure\Persistence\MySQL\Doctrine;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Infrastructure\Persistence\MySQL\Doctrine\PostEntity;


#[ORM\Table(name: "Account")]
#[ORM\Entity]
class AccountEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "guid", unique: true)]
    public ?int $id = null;

    #[ORM\Column(length: 150)]
    public ?string $name = null;

    #[ORM\Column(length: 255, unique: true, nullable: false)]
    public ?string $email = null;

    #[ORM\Column(length: 255, nullable: false)]
    public ?string $siret = null;

    #[ORM\Column(length: 255, nullable: false)]
    public ?string $password = null;

    #[ORM\OneToMany(mappedBy: "post", targetEntity: PostEntity::class )]
    public Collection $posts;

    public static function create(
        string $id,
        string $name,
        string $email,
        string $password,
        string $siret
    ): self {
        $entity = new self();
        $entity->id       = $id;
        $entity->name     = $name;
        $entity->email    = $email;
        $entity->password = $password;
        $entity->siret    = $siret;
        return $entity;
    }


    public function getId(): string { return $this->id; }

    public function getName(): string { return $this->name; }

    public function getEmail():string { return $this->email; }

    public function getPassword():string { return $this->password; }

    public function getSiret(){ return $this->siret; }
    


    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(string $hash): void
    {
        $this->password = $hash;
    }


    public function setSiret(string $siret): void
    {
        $this->siret = $siret;
    }

}
