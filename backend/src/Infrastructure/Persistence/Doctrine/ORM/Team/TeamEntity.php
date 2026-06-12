<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Team;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: "recruiter_team")]
class TeamEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;
}