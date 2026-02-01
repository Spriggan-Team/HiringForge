<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\MappedSupperClass;

use Doctrine\ORM\Mapping as ORM;

/**
 * This is a technical Class used to simplify Actor definiton (ex: User, Candidate, Agent)
 */
#[ORM\MappedSuperclass]
class Actor
{
    #[ORM\Id]
    #[ORM\Column(type: "guid", unique: true)]
    private string $id;

    #[ORM\Column(length: 255, unique: true, nullable: false)]
    private string $email;

    #[ORM\Column(length: 255, nullable: false)]
    private string $password;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    //---------------
    //  GETTER
    //--------------
    public function getId(): string { return $this->id; }


    public function getEmail():string { return $this->email; }

    public function getPassword():string { return $this->password; }

    public function getDescription(): string { return $this->description; }
    
    //---------------------------
    //  SETTERS
    //--------------------------

    public function setId(string $id): static
    {
        $this->id = $id;
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

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }
}