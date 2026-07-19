<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill;


use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: "skills")]
class SkillEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $label;


    public function __constrcut(
        string $label,
        ? string $id = null,
    ){}

    //-- GETTERS

    public function getId(){
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    //-- SETTER

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }


}