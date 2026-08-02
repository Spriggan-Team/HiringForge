<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillEntity;
use Doctrine\ORM\Mapping as ORM;



#[ORM\Entity]
#[ORM\Table(name: "job_offer_skills")]
class JobOfferSkillsEntity
{
    #[ORM\Id]
    #[ORM\ManyToOne(
        targetEntity: JobOfferEntity::class,
        inversedBy: "skills"
    )]
    #[ORM\JoinColumn(
        name: "job_offer_id",
        referencedColumnName: "id",
        nullable: false, onDelete: "CASCADE"
    )]
    private JobOfferEntity $jobOffer;

    #[ORM\Id]
    #[ORM\ManyToOne(
        targetEntity: SkillEntity::class
    )]
    #[ORM\JoinColumn(
        name: "skill_id",
        referencedColumnName: "id", 
        nullable: false,
    )]
    private SkillEntity $skill;


    #[ORM\Column(length: 20)]
    private bool $isRequired  = true;


    //------------------------
    //-----CONSTRUCTOR/BUILDING
    //-------------------------

    public function __construct(    
        JobOfferEntity $jobOffer,
        SkillEntity $skill,
        bool $isRequired = true
    ){
        $this->jobOffer = $jobOffer;
        $this->skill = $skill; 
        $this->isRequired = $isRequired;
    }


    public static function create(
        JobOfferEntity $jobOffer,
        SkillEntity $skill,
        bool $isRequired = true
    ){
        return new self(
            jobOffer: $jobOffer,
            skill: $skill,
            isRequired: $isRequired
        );
    }

    //---------------------------
    //--- GETTERS
    //---------------------------

    public function getJobOffer() {
        return $this->jobOffer;
    }

    public function getSkill()  {
        return $this->skill;
    }

    public function isRequired(){
        return $this->isRequired;
    }

    //-------------------
    //------SETTERS
    //---------------------

    public function setJobOffer(JobOfferEntity $jobOffer)
    {
        $this->jobOffer = $jobOffer;
        return $this;
    }

    public function setSkill(SkillEntity $skill)
    {
        $this->skill = $skill;
        return $this;
    }

    public function setIsRequired(bool $isRequired)
    {
        $this->isRequired = $isRequired;
        return $this;
    }

}