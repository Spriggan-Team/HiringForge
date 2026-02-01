<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\File;

use App\Domain\Candidate\Candidate;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserImageEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\DependencyInjection\Attribute\Target;

#[ORM\Entity]
#[ORM\Table(
        name: 'file',
    )
]
class FileEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(length: 250)]
    private string $originalName;

    #[ORM\Column(length:15)]
    private string $mime;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    private float $size;

 

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    //------------------------
    //-Relations
    //---------------------------
    
    #[ORM\OneToOne(
        targetEntity: Candidate::class,
        mappedBy: 'image'
    )]
    private ?CandidateEntity $candidateImage = null;

    #[ORM\OneToOne(
        targetEntity: Candidate::class,
        mappedBy: 'cv'
    )]
    private ?CandidateEntity $candidateCV = null;

    #[ORM\OneToOne(
        mappedBy: 'presentation',
        targetEntity: UserEntity::class
    )]
    private ?UserEntity $userPresentation = null;

    #[ORM\OneToMany(
        mappedBy: "image",
        targetEntity: UserImageEntity::class, 
        cascade:['persist', 'remove']
    )]
    private Collection $userImages;


    //-------------------
    //  Construct
    //-------------------------

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->userImages = new ArrayCollection();
    }
    
    /* =======================
     * GETTERS
     * ======================= */
    
    public function getId(){
        return $this->id;
    }
    
    public function getOriginalName() {
        return $this->originalName;
    }
    

    
    public function getMime(){
        return $this->mime;
    }

    public function getSize(){
        return $this->size;
    }
    public function getCreatedAt(){
        return $this->createdAt;
    }

    //------Collection

    public function getUserImages()
    {
        return $this->userImages;
    }
    
    /* =======================
     * SETTERS
     * ======================= */

    public function setId(?int $id): static
    {
        $this->id = $id;
        return $this;     
    }

    public function setOriginalName(string $originalName):static
    {
        $this->originalName = $originalName;
        return $this;
    }
    
    public function setMime(string $mime):static
    {
        $this->mime = $mime;
        return $this;
    }    
    
    
    public function setSize(float $size):static
    {
        $this->size = $size;
        return $this;
    }


}