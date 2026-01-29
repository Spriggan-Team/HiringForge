<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\User;

use App\Infrastructure\Persistence\Doctrine\ORM\Global\MappedSupperClass\Actor;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Image\ImageEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;


#[ORM\Table(name: "user")]
#[ORM\Entity]
class UserEntity extends Actor
{
    //------------------------
    // Extra  Columns
    //-----------------------

    #[ORM\Column(length: 255, nullable: false)]
    private string $siret;


    //------------------------
    //  Relations
    //-----------------------


    #[ORM\OneToOne(
        targetEntity: UserAddressEntity::class,
        mappedBy: 'user',
        cascade: ['persist', 'remove']
    )]
    #[ORM\JoinColumn(nullable: false)]
    private UserAddressEntity $userAddress;

    #[ORM\OneToMany(
        mappedBy: "user",
        targetEntity: JobOfferEntity::class
    )]
    private Collection $jobOffers;

    #[ORM\OneToMany(
        mappedBy: "user",
        targetEntity: UserImageEntity::class,
        cascade:['persist', 'remove']
    )]
    private Collection $userImages;


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


    public function getAddress(): AddressEntity { 
        return $this->userAddress->getAdrdress();
    }

    public function getSiret(){ return $this->siret; }

    
    public function getJobOffer(): Collection { return $this->jobOffers; }
    public function getUserImages(): Collection { return $this->userImages; }

    /* =======================
     * SETTERS
     * ======================= */


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

    public function attachToImage(ImageEntity $image): static
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

    public function removeImage(ImageEntity $image): void
    {
        foreach ($this->userImages as $userImage) {
            if ($userImage->getImage() === $image) {
                $this->userImages->removeElement($userImage);
            }
        }
    }
}
