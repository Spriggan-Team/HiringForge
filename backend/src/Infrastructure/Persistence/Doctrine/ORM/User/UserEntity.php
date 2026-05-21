<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\User;


use App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account\AccountEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\File\FileEntity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\JoinColumn;


#[ORM\Table(name: "user")]
#[ORM\Entity]
class UserEntity extends AccountEntity
{
    //------------------------
    // Extra  Columns
    //-----------------------

    
    #[ORM\Column(length: 150)]
    private string $name;

    #[ORM\Column(length: 255, nullable: false)]
    private string $siret;

    //------------------------
    //  Relations
    //-----------------------

    #[ORM\OneToOne(
        inversedBy: 'userVideoPresentation',
        targetEntity: FileEntity::class,
        cascade: ['persist', 'remove']
    )]
    #[JoinColumn(nullable: true)]
    private ?FileEntity $videoPresentation = null;


    #[ORM\OneToOne(
        targetEntity: UserAddressEntity::class,
        mappedBy: 'user',
        cascade: ['persist', 'remove']
    )]
    private UserAddressEntity $userAddress;


    #[ORM\OneToMany(
        mappedBy: "user",
        targetEntity: JobOfferEntity::class
    )]
    private ?Collection $jobOffers = null;

    #[ORM\OneToMany(
        mappedBy: "user",
        targetEntity: UserImageEntity::class,
        cascade:['persist', 'remove']
    )]
    private ?Collection $userImages = null;


    //------------------------
    //  Construction...
    //-----------------------
    
    public function __construct()
    {
        $this->jobOffers = new ArrayCollection();
        $this->userImages = new ArrayCollection();
    }

    public static function create(
        string $id,
        string $name,
        string $email,
        string $password,
        string $siret,
        AddressEntity $address,
    ): self {
        $entity = new self();
        $entity->setId($id)
               ->setName($name)
               ->setEmail($email)
               ->setPassword($password)
               ->setSiret($siret)
               ->attachToAddress($address);
        return $entity;
    }

    /* =======================
     * GETTERS
     * ======================= */

    public function getName(): string { return $this->name; }

    public function getAddress(): AddressEntity { 
        return $this->userAddress->getAdrdress();
    }

    public function getSiret(){ return $this->siret; }

    public function getVideoPresentation(){ return $this->videoPresentation; }

    public function getJobOffer(): Collection { return $this->jobOffers; }
    public function getUserImages(): Collection { return $this->userImages; }

    /* =======================
     * SETTERS
     * ======================= */

    public function setName(string $name): static { 
        $this->name = $name;
        return $this;    
    }

    public function setSiret(string $siret): static{
        $this->siret = $siret;
        return $this;    
    }
    //--------------------------------
    // Utils
    //--------------------------------

    public function attachToAddress(AddressEntity $address): static
    {
        $userAddress = new UserAddressEntity($this, $address);
        $this->userAddress = $userAddress;
        return $this;
    }

    public function attachToImage(FileEntity $image): static
    {
        //Forbide duplicates
        foreach($this->userImages as $image){
            if($image->getImage() === $image){
                return $this;
            }
        }
        $userImage = new UserImageEntity($this, $image);
        $this->userImages->add($userImage);
        return $this;
    }
    

    public function removeImage(FileEntity $image): void
    {
        foreach ($this->userImages as $userImage) {
            if ($userImage->getImage() === $image) {
                $this->userImages->removeElement($userImage);
            }
        }
    }

    public function attachPresentation(FileEntity $video): static
    {
        if(!str_contains($video->getMime(), 'video')){
            return $this;
        }
        $this->videoPresentation = $video;
        return $this;
    }
}
