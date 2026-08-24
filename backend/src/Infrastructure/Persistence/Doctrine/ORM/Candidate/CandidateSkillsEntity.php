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
        targetEntity: CandidateEntity::class,
        inversedBy: "skills"
    )]
    #[ORM\JoinColumn(
        name: "candidate_id",
        referencedColumnName: "id",
        nullable: false,
    )]
    private CandidateEntity $candidate;


    #[ORM\Id]
    #[ORM\ManyToOne(
        targetEntity: SkillEntity::class
    )]
    #[ORM\JoinColumn(
        name: "skill_id",
        referencedColumnName: "id",
    )]
    private SkillEntity $skill;


    #[ORM\ManyToOne(
        targetEntity: CandidateResumeEntity::class,
    )]
    #[ORM\JoinColumn(
        nullable: true
    )]
    private ?CandidateResumeEntity $candidateResume = null;

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
        ?CandidateResumeEntity $candidateResume =null
    )
    {
        $entity = new self(
            source: $source,
            skill: $skill,
            candidate: $candidate,
        );
        $entity->setCandidateResume($candidateResume);
        return $entity ;
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

    public function getCandidateResume(){
        return $this->candidateResume;
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

    public function setCandidateResume(?CandidateResumeEntity $candidateResume){
        $this->candidateResume = $candidateResume;
        return $this;
    }


}