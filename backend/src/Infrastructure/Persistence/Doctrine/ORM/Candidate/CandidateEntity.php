<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AccountEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferViewEntity;
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


    #[ORM\OneToOne(
        inversedBy: 'candidateCV',
        targetEntity: FileEntity::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    #[ORM\JoinColumn(nullable: true)]
    private ?FileEntity $cv = null;


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
    
    //------------------------
    //  Construction...
    //-----------------------

    public function __construct()
    {
        $this->interviews  = new ArrayCollection();
        $this->applications = new ArrayCollection();
        $this->jobOfferViews = new ArrayCollection();
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
    public function getCV(): ?FileEntity {
        return $this->cv;
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
    
    public function attachCV(FileEntity $cv): static {
        $this->cv = $cv;
        return $this;
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