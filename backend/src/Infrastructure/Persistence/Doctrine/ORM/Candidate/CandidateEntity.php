<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;

use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferViewEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AccountEntity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;



#[ORM\Entity]
#[ORM\Table(name: "candidate",)]
class CandidateEntity extends AccountEntity
{
    //------------------------
    // Extra  Columns
    //-----------------------

    #[ORM\Column(type: 'integer')]
    private int $searchRadius;

    //---------------------------------
    //-------Relations
    //--------------------------------


    #[ORM\OneToMany(
        mappedBy: 'candidate',
        targetEntity: ApplicationEntity::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    private Collection $applications;


    #[ORM\OneToOne(targetEntity: AddressEntity::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'address_id', nullable: true)]
    private ?AddressEntity $address = null;


    #[ORM\OneToMany(mappedBy: "candidate", targetEntity: InterviewEntity::class)]
    private Collection $interviews;


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
        $this->interviews  = new ArrayCollection();
        $this->applications = new ArrayCollection();
        $this->jobOfferViews = new ArrayCollection();
        $this->resumes = new ArrayCollection();
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
    public function getInterviews():    Collection  { return $this->interviews; }

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
    
    /* =======================
     * SETTERS
     * ======================= */


    public function setLastName(string $lastName): static {
        $this->lastName = $lastName;
        return $this;
    }

        public function setFirstName(string $firstName): static {
        $this->firstName = $firstName;
        return $this;
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

    }

    public function attachToAddress(AddressEntity $address):static
    {
        $this->address = $address;
        return $this;
    }

    public function addJobViews(JobOfferViewEntity $jobOfferViews): self{
        $this->jobOfferViews->add($jobOfferViews);
        return $this;
    }
}