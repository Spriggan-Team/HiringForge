<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer;

use Doctrine\ORM\Mapping as ORM;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;


#[ORM\Entity]
#[ORM\Table('job_offer_images')]
class JobOfferImageEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(
        targetEntity: JobOfferEntity::class,
        inversedBy: 'images'
    )]
    #[ORM\JoinColumn(nullable: false)]
    private JobOfferEntity $jobOffer;

    #[ORM\OneToOne(
        targetEntity: FileEntity::class,
        cascade: ['persist'],
        inversedBy: "jobOfferImage",
        orphanRemoval: true
    )]
    #[ORM\JoinColumn(nullable: false)]
    private FileEntity $file;


    #[ORM\Column(type: 'boolean')]
    private bool $isMain = false;

    //-----------------
    // Construc
    //-----------------------------------
    public function __construct(JobOfferEntity $jobOffer, FileEntity $file, bool $isMain = false)
    {
        $this->jobOffer = $jobOffer;
        $this->file = $file;
        $this->isMain = $isMain;
    }

    //-------------
    //--- GETTERS
    //----------------------

    public function getId(){
        return $this->id;
    }

    public function getIsMain(): bool
    {
        return $this->isMain;
    }

    public function setIsMain(bool $isMain): void
    {
        $this->isMain = $isMain;
    }

    public function getFile(): FileEntity
    {
        return $this->file;
    }

    public function getJobOffer(): JobOfferEntity
    {
        return $this->jobOffer;
    }
}
