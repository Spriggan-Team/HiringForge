<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill;


use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: "skills")]
class SkillEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid")]
    #[ORM\GeneratedValue("CUSTOM")]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string  $id = null;

    #[ORM\Column(length: 120)]
    private string $name;
    
    #[ORM\Column(length: 120)]
    private string $slug;

    #[ORM\Column(type: "boolean")]
    private bool $isDefault  = true;


    //---------------------
    //--- Construct
    //----------------------

    public function __construct(
        string $name,
        string $slug,
        bool $isDefault = true,
        ? string $id = null,
    ){
        $this->id = $id;
        $this->slug = $slug;
        $this->name = $name;
        $this->isDefault = $isDefault ;
    }

    public static function create(
        string $name,
        string $slug,
        bool $isDefault = true,
        ? string $id = null,
    ){
        return new self(
            id: $id,
            slug: $slug,
            name: $name,
            isDefault: $isDefault,
        );
    }

    //-- GETTERS
    public function getId(){
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(){
        return $this->slug;
    }

    public function isDefault()
    {
        return $this->isDefault;    
    }

    //----------------------
    //-- SETTER
    //--------------------------


    public function setName(string $name){
        $this->name = $name;
        return $this;
    }

    public function setLabel(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function setSlug(string $slug){
        $this->slug = $slug;
        return $this;
    }

    public function setIsDefault(bool $isDefault)
    {
        $this->isDefault = $isDefault;
        return $this;
    }

}