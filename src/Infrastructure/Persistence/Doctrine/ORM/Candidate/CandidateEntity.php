<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\MappedSupperClass\AccountEntity;

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

    #[ORM\Column(length: 255, nullable: false)]
    private string $lastName;

    #[ORM\Column(length: 255, nullable: false)]
    private string $firstName;

    #[ORM\Column(type: 'integer')]
    private int $searchRadius;

    //---------------------------------
    //-------Relations
    //--------------------------------

    #[ORM\OneToOne(
        inversedBy: 'candidateImage',
        targetEntity: FileEntity::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    #[ORM\JoinColumn(nullable: true)]
    private ?FileEntity $image = null;


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


    #[ORM\OneToOne(
        inversedBy: "candidate",
        targetEntity: AddressEntity::class,
        cascade: ['persist', 'remove']
    )]
    private AddressEntity $address;


    #[ORM\OneToMany(mappedBy: "candidate", targetEntity: InterviewEntity::class)]
    private Collection $interviews;

    
    //------------------------
    //  Construction...
    //-----------------------

    public function __construct()
    {
        $this->interviews  = new ArrayCollection();
        $this->applications = new ArrayCollection();
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
}