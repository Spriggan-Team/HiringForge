<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Agent;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AccountEntity;


#[ORM\Entity]
#[ORM\Table(name: "agent")]
class AgentEntity extends AccountEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\OneToMany(
        mappedBy: "agent",
        targetEntity: AgentAssignementEntity::class,
        cascade: ["persist", "remove"]
    )]
    private Collection $agentAssignments;

    public function __construct()
    {
        parent::__construct();
        $this->agentAssignments = new ArrayCollection();
    }

    //----------
    //--- SETTERS
    //----------


    /**
     * @return Collection<int, AgentAssignementEntity>
     */
    public function getAgentAssignments(): Collection
    {
        return $this->agentAssignments;
    }

    public function addAssignment(AgentAssignementEntity $assignment): self
    {
        if (!$this->agentAssignments->contains($assignment)) {
            $this->agentAssignments->add($assignment);
            $assignment->setAgent($this);
        }

        return $this;
    }

    public function removeAssignment(AgentAssignementEntity $assignment): self
    {
        if ($this->agentAssignments->removeElement($assignment)) {
            if ($assignment->getAgent() === $this) {
                $assignment->setAgent($this);
            }
        }

        return $this;
    }
}