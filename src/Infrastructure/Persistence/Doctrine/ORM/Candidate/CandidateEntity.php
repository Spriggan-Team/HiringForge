<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Candidate;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;



#[ORM\Entity]
#[ORM\Table(name: "candidate",)]
class CandidateEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid', unique: true)]
    private string $id;

    #[ORM\Column(length: 255, nullable: false)]
    private string $lastName;

    #[ORM\Column(length: 255, nullable: false)]
    private string $firstName;

    #[ORM\Column(length: 255, nullable: false)]
    private string $email;
    
    #[ORM\Column(length: 255, nullable: false)]
    private string $password;


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
    #[ORM\JoinColumn(nullable: false)]
    private FileEntity $cv;

    #[ORM\OneToMany(
        mappedBy: 'candidate',
        targetEntity: ApplicationEntity::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    private Collection $applications;

    #[ORM\OneToMany(mappedBy: "candidate", targetEntity: InterviewEntity::class)]
    private Collection $interviews;

    
    public function __construct()
    {
        $this->interviews  = new ArrayCollection();
        $this->applications = new ArrayCollection();
    }


    //------GETTERS

    public function getId(): string { return $this->id; }
    public function getLastName():        string  { return $this->lastName; }
    public function getFirstName():       string  { return $this->firstName; }

    public function getEmail():           string  { return $this->email; }
    public function getPassword():        string  { return $this->password; }

    public function getImage(): ?FileEntity{ 
        return $this->image;
    }
    public function getCV(): FileEntity {
        return $this->cv;
    }

    public function getApplication():   Collection  { return $this->applications; }
    public function getInterviews():    Collection  { return $this->interviews; }

    //-------SETTERS

    public function setId(string $id): static {
        $this->id = $id;
        return $this;
    }

    public function setLastName(string $lastName): static {
        $this->lastName = $lastName;
        return $this;
    }

        public function setFirstName(string $firstName): static {
        $this->firstName = $firstName;
        return $this;
    }

    public function setEmail(string $email): static {
        $this->email = $email;
        return $this;
    }

    public function setPassword(string $password): static {
        $this->password = $password;
        return $this;
    }     
    
    public function attachImage(FileEntity $image): static {
        $this->image = $image;
        return $this;
    } 
    
    public function attachCV(FileEntity $cv): static {
        $this->cv = $cv;
        return $this;
    }
}