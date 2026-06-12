<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Company;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;



#[ORM\Entity]
#[ORM\Table(name: "company")]
class CompanyEntity
{
    #[ORM\Id]
    #[ORM\Column(type: "guid", unique: true)]
    private ?string $id = null;

    #[ORM\Column(length: 150)]
    private string $name;

    #[ORM\Column(length: 255, nullable: false)]
    private string $siret;

    #[ORM\Column()]
    private \DateTimeImmutable $createdAt;

    //---------------------
    //------- Relations
    //---------------------

    #[ORM\OneToOne(
        inversedBy: 'companyLogo',
        targetEntity: FileEntity::class,
        cascade: ['persist', 'remove']
    )]
    #[ORM\JoinColumn(nullable: true)]
    private ?FileEntity $logo = null;


    #[ORM\OneToOne(
        inversedBy: 'companyVideoPresentation',
        targetEntity: FileEntity::class,
        cascade: ['persist', 'remove']
    )]
    #[ORM\JoinColumn(nullable: true)]
    private ?FileEntity $videoPresentation = null;


    #[ORM\OneToMany(
        targetEntity: CompanyAddressEntity::class,
        mappedBy: 'company',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $companyAddresses;


    #[ORM\OneToMany(
        mappedBy: "company",
        targetEntity: CompanyImageEntity::class,
        cascade:['persist', 'remove']
    )]
    private ?Collection $companyImages = null;


    #[ORM\OneToMany(
        mappedBy: "company",
        cascade:['persist', 'remove'],
        targetEntity: UserEntity::class
    )]
    private Collection $recruiters;

    //-----------------
    //----- Construct
    //-------------------
    
    
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->companyImages = new ArrayCollection();
        $this->companyAddresses = new ArrayCollection();
    }

    public static function create(
        string $id,
        string $name,
        string $siret,
        ?FileEntity $logo = null,
        ?AddressEntity $address = null,
        ?FileEntity $videoPresentation = null,
    ): self {
        $entity = new self()
                    ->setId($id)
                    ->setLogo($logo)
                    ->setName($name)
                    ->setSiret($siret)
                    ->attachPresentation($videoPresentation);
        if($address)
            $entity->addAddress($address);
        
        return $entity;
    }
    

    //------------------
    //---- GETTERS. 
    //-------------------
    public function getId(){
        return $this->id;
    }

    public function getName() { return $this->name; }

    public function getSiret(){ return $this->siret; }

    public function getLogo(): ?FileEntity { return $this->logo; } 
    
    public function getAddresses(): Collection{
        return $this->companyAddresses;
    }

    public function getCompanyAddress(): Collection { 
        return $this->companyAddresses;
    }

    public function getVideoPresentation(){
        return $this->videoPresentation;
    }

    public function getImages(){
        return $this->companyImages;
    }

    public function getCreatedAt(){
        return $this->createdAt;
    }

    public function getRecruiters(){
        return $this->recruiters;
    }

    //--------------------------------
    // Utils / SETTERS
    //--------------------------------

    public function setId(string $id): self{
        $this->id = $id;
        return $this;
    }

    public function setName(string $name): self{
        $this->name = $name;
        return $this;
    }

    public function setSiret(string $siret): static{
        $this->siret = $siret;
        return $this;    
    }

    public function setLogo(?FileEntity $logo): static{
        $this->logo = $logo;
        return $this;
    }

    public function addRecruiters(UserEntity $user){
        $this->recruiters->add($user);
        return $this;
    }

    public function addAddress(AddressEntity $address): static
    {
        $companyAddress = new CompanyAddressEntity($this, $address);
        $this->companyAddresses->add($companyAddress);
        return $this;
    }


    public function attachToImage(?FileEntity $image): static
    {
        if(!$image)
            return $this;

        //Forbide duplicates
        foreach($this->companyImages as $img){
            if($img->getImage() === $image){
                return $this;
            }
        }
        $companyImages = new CompanyImageEntity($this, $image);
        $this->companyImages->add($companyImages);
        return $this;
    }
    

    public function removeImage(FileEntity $image): void
    {
        foreach ($this->companyImages as $userImage) {
            if ($userImage->getImage() === $image) {
                $this->companyImages->removeElement($userImage);
            }
        }
    }

    public function attachPresentation(?FileEntity $video): static
    {
        if($video && !str_contains($video->getMime(), 'video')){
            return $this;
        }
        $this->videoPresentation = $video;
        return $this;
    }
}