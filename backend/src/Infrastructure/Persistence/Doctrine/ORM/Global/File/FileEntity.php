<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\File;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferImageEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserImageEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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
    /** @var string  $name a uniq name - genrated while uploading file on server */
    private ?string $name = null;

    #[ORM\Column(length:15)]
    private ?string $mime = null;

    #[ORM\Column(type: "float", precision: 10, scale: 2)]
    private ?float $size = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    //------------------------
    //-Relations
    //---------------------------
    
    #[ORM\OneToOne(
        targetEntity: CandidateEntity::class,
        mappedBy: 'image'
    )]
    private ?CandidateEntity $candidateImage = null;

    #[ORM\OneToOne(
        targetEntity: CandidateEntity::class,
        mappedBy: 'cv'
    )]
    private ?CandidateEntity $candidateCV = null;

    #[ORM\OneToOne(
        mappedBy: 'videoPresentation',
        targetEntity: UserEntity::class
    )]
    private ?UserEntity $userVideoPresentation = null;

    #[ORM\OneToOne(
        targetEntity: JobOfferImageEntity::class,
        inversedBy: 'file'
    )]
    private JobOfferImageEntity $jobOfferImage;

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
    
    public function getId():int
    {
        return $this->id;
    }
    
    /** This function return a string that represent the original  and uniq name genrated By the server
     *  when the file was uploaded
     */
    public function getName():string
    {
        return $this->name;
    }
    
    /**
     * This function return a string that represent the mime type of the designated file
     */
    public function getMime():string
    {
        return $this->mime;
    }

    /**
     * This function return the fyle size (wich must be stored in bytes format)
     */
    public function getSize(): float {
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


    /**
     * Here you must insert an originame and uniq name (genreated by your server or your system)
     */
    public function setName(string $name):static
    {
        $this->name = $name;
        return $this;
    }
    
    public function setMime(string $mime):static
    {
        $this->mime = $mime;
        return $this;
    }    
    
    /**
     * Here you can define a size for you file to be recorded.
     * But this size must be in bytes
     */
    public function setSize(float $size):static
    {
        $this->size = $size;
        return $this;
    }


}