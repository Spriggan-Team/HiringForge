<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate;

use App\Domain\Candidate\CandidateStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;

use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferViewEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AccountEntity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\OneToMany;


#[ORM\Entity]
#[ORM\Table(name: "candidate",)]
class CandidateEntity extends AccountEntity
{
    //------------------------
    // Extra  Columns
    //-----------------------
     

    #[ORM\Column(enumType: CandidateStatus::class)]
    private CandidateStatus $status = CandidateStatus::ACTIVE;

    #[ORM\Column(type: 'integer')]
    private int $searchRadius;


    //---------------------------------
    //-------Relations
    //--------------------------------

    #[OneToMany(
        targetEntity: CandidateSkillsEntity::class,
        mappedBy: "candidate"
    )]
    /**
     * @var Collection<int,CandidateSkillsEntity> $skills
     */
    private Collection $skills;

    #[ORM\OneToMany(
        mappedBy: 'candidate',
        targetEntity: ApplicationEntity::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    private Collection $applications;


    #[ORM\OneToOne(
        targetEntity: AddressEntity::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ORM\JoinColumn(name: 'address_id', nullable: true)]
    private AddressEntity $address;


    #[ORM\OneToMany(
        mappedBy: "candidate",
        targetEntity: JobOfferViewEntity::class,
        cascade:['persist', 'remove']
    )]
    private Collection $jobOfferViews;


    #[ORM\OneToMany(
        mappedBy: "candidate",
        targetEntity: CandidateResumeEntity::class,
        cascade:['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $resumes;
    
    //------------------------
    //  Construction...
    //-----------------------

    public function __construct()
    {
        parent::__construct();
        $this->applications = new ArrayCollection();
        $this->jobOfferViews = new ArrayCollection();
        $this->resumes = new ArrayCollection();
        $this->skills = new ArrayCollection();
    }

    public static function create()
    {}

    /* =======================
     * GETTERS
    * ======================= */

    public function getLastName():        string  { return $this->lastName; }
    public function getFirstName():       string  { return $this->firstName; }


    public function getImage(): ?FileEntity{ 
        return $this->image;
    }

    /** @return Collection<int, CandidateResumeEntity> */
    public function getResumes(): Collection {
        return $this->resumes;
    }

    public function getApplication():   Collection  { return $this->applications; }

    public function getAddress(): AddressEntity
    {
        return $this->address;
    }


    public function getSearchRadius(): int {
        return $this->searchRadius;
    }

    public function getJobOfferViews(): Collection{
        return $this->jobOfferViews;
    }

    /**
     * @return Collection<int,CandidateSkillsEntity>
     */
    public function getSkills(){
        return $this->skills;
    }
    
    /* =======================
     * SETTERS
     * ======================= */

    //-- basic

    public function setLastName(string $lastName): static {
        $this->lastName = $lastName;
        return $this;
    }

        public function setFirstName(string $firstName): static {
        $this->firstName = $firstName;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }


    public function setSearchRadius(int $searchRadius): static
    {
        $this->searchRadius = $searchRadius;
        return $this;
    }



    //--------------------------------
    // Utils
    //--------------------------------

    public function attachImage(FileEntity $image): static {
        $this->image = $image;
        return $this;
    } 
    
    //-- Resume
    public function addResume(CandidateResumeEntity $resume): static {
        if($this->resumes->contains($resume)){
            return $this;
        }
        $this->resumes->add($resume);
        return $this;
    }

    public function removeResume(CandidateResumeEntity $resume){
        if($this->resumes->contains($resume)){
            $this->resumes->removeElement($resume);
            return $this;
        }
        return $this;
    }

    public function clearResume(){
        return $this;
    }

    //-- Address
    public function attachToAddress(AddressEntity $address):static
    {
        $this->address = $address;
        return $this;
    }

    public function setAddress(AddressEntity $address){
        $this->address = $address;
        return $this;
    }

    //-- JobViews
    public function addJobViews(JobOfferViewEntity $jobOfferViews): self{
        $this->jobOfferViews->add($jobOfferViews);
        return $this;
    }

    public function setStatus(CandidateStatus $status){
        $this->status = $status;
    }

    //-- Skills
    public function addSkill() : static {
        return $this;
    }

    public function removeSkill(): static
    {
        return $this;
    }
}