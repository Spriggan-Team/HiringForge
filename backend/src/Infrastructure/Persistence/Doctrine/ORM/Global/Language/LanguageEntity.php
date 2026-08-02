<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Language;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: "languages")]
class LanguageEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;
    
    #[ORM\Column(length: 15)]
    private string $code;
    
    #[ORM\Column(length: 99)]
    private ?string $label;

    //---------------------------
    // Constructing
    //------------------------
    
    public function __construct(){}

    public static function create(
        string $code,
        ?string $label = null
    ): self {
        $entity = new self();

        $entity->code = $code;
        $entity->label = $label;

        return $entity;
    }

    public static function reconstitute(
        int $id,
        string $code,
        ?string $label = null
    ): self {
        $entity = new self();

        $entity->id = $id;
        $entity->code = $code;
        $entity->label = $label;

        return $entity;
    }

    //---------
    //--- GETTERS
    //------------------
    
    public function getId(){
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    //--------------------
    //-- SETTERS
    //----------------------
    
    public function setLabel(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function setCode(string $code): static
    {
        $this->code = strtolower($code);

        return $this;
    }

}