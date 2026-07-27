<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillEntity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: "candidate_skills",)]
class CandidateSkillsEntity
{

    #[ORM\Column(length: 20)]
    private string $source = "parsed"; //-- values: parsed, manual

    //----------------------------
    //--- RELATIONS
    //-------------------------------

    #[ORM\Id]
    #[ORM\ManyToOne(
        targetEntity: CandidateEntity::class
    )]
    #[ORM\JoinColumn(
        name: "candidate_id",
        referencedColumnName: "id",
        nullable: false,
        onDelete: "CASCADE"
    )]
    private CandidateEntity $candidate;


    #[ORM\Id]
    #[ORM\ManyToOne(
        targetEntity: SkillEntity::class
    )]
    #[ORM\JoinColumn(
        name: "skill_id",
        referencedColumnName: "id",
        onDelete: "CASCADE"
    )]
    private SkillEntity $skill;

    //----------------------------
    //- CONSTRUCTING/ BUILDING
    //-----------------------------

    public function __construct(
        CandidateEntity $candidate,
        SkillEntity $skill,
        string $source = "parsed",
    ){
        $this->source = $source;
        $this->candidate = $candidate;
        $this->skill = $skill;
    }

    public static function create(
        CandidateEntity $candidate,
        SkillEntity $skill,
        string $source = "parsed",
    )
    {
        return new self(
            source: $source,
            skill: $skill,
            candidate: $candidate,
        );
    }

    //---------------------
    //- GETTERS
    //---------------------

    public function getSource()
    {
        return $this->source;
    }

    public function getCandidate()
    {
        return $this->candidate;
    }

    public function getSkill()
    {
        return $this->skill;
    }

    //------------------------
    //--- SETTERS
    //------------------------

    public function setSource(string $source)
    {
        $this->source = $source;
        return $this;
    }

    public function setCandidate(CandidateEntity $candidate)
    {
        $this->candidate = $candidate;
        return $this;
    }

    public function setSkill(SkillEntity $skill)
    {
        $this->skill = $skill;
        return $this;
    }
}