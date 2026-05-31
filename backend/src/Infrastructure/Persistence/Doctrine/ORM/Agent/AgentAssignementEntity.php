<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Agent;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\EntityType;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: "agent_assignment")]
class AgentAssignementEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: "agentAssignments", targetEntity: AgentEntity::class)]
    #[ORM\JoinColumn(nullable: false, name: "agent_id")]
    private AgentEntity $agent;

    #[ORM\Column(enumType: EntityType::class)]
    private EntityType $entityType;

    #[ORM\Column(type: "guid")]
    private string $assignedBy;

    #[ORM\Column(type: "datetime_immutable")]
    private DateTimeImmutable $expiredAt;

    #[ORM\Column(type: "datetime_immutable")]
    private DateTimeImmutable $assignedAt;


    //-------------------------
    //----------  GETTERS
    //--------------------------

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAgent(): AgentEntity
    {
        return $this->agent;
    }

    public function getAssignedBy(): string
    {
        return $this->assignedBy;
    }


    public function getEntityType(): EntityType
    {
        return $this->entityType;
    }


    public function getExpiredAt(): DateTimeImmutable
    {
        return $this->expiredAt;
    }


    public function getAssignedAt(): DateTimeImmutable
    {
        return $this->assignedAt;
    }
    
    //-------------------------
    //----------  SETTERS
    //--------------------------
    
    public function setAgent(AgentEntity $agent): self
    {
        $this->agent = $agent;
        return $this;
    }

    public function setEntityType(EntityType $entityType): self
    {
        $this->entityType = $entityType;
        return $this;
    }


    public function setAssignedBy(string $assignedBy): self
    {
        $this->assignedBy = $assignedBy;
        return $this;
    }



    public function setExpiredAt(DateTimeImmutable $expiredAt): self
    {
        $this->expiredAt = $expiredAt;
        return $this;
    }


    public function setAssignedAt(DateTimeImmutable $assignedAt): self
    {
        $this->assignedAt = $assignedAt;
        return $this;
    }
}