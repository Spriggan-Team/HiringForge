<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\User;

use App\Domain\User\UserRole;
use Doctrine\ORM\Mapping as ORM;



#[ORM\Entity]
#[ORM\Table(name: "role")]
class UserRoleEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 100, unique: true, enumType: UserRole::class)]
    private UserRole $name;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $description = null;


    public static function create(UserRole $name, ?string $description = null): self
    {
        $entity = new self();
        $entity->name = $name;
        $entity->description = $description;
        return $entity;
    }
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): UserRole
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }
}