<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Address;

use App\Infrastructure\Persistence\Doctrine\ORM\User\UserAddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;



#[ORM\Entity]
#[ORM\Table(name: "address")]
class AddressEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $street;

    #[ORM\Column(length: 255)]
    private string $city;

    #[ORM\Column(length: 255)]
    private string $postalCode;

    #[ORM\Column(length: 255)]
    private string $country;

    //--------------------
    // Relations
    //---------------------

    #[ORM\OneToOne(
        targetEntity: UserAddressEntity::class,
        mappedBy: 'address'
    )]
    private UserAddressEntity $userAddress;

    //-----------------
    // Builder
    //------------------
    
    public static function create(
        string $city,
        string $street,
        string $postalCode,
        string $country,
    ): static
    {
        $address = new self();
        $address->setCity($city)
                ->setStreet($street)
                ->setPostalCode($postalCode)
                ->setCountry($country);
        return $address;
    }


    //===================================
    //      GETTERS
    //=====================================

    public function getId(): int
    {
        return $this->id;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getPostalCode(): string{
        return $this->postalCode;
    }

    public function getCity(): string{
        return $this->city;
    }

    public function getCountry(): string{
        return $this->country;
    }

    public function getUserAdress(): UserAddressEntity
    {
        return $this->userAddress;
    }

    //=================================
    //      SETTERS
    //=================================


    public function setStreet(string $street): static
    {
        $this->street = $street;
        return $this;
    }

    public function setPostalCode(string $postalCode): static
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;
        return $this;
    }



    public function setUserAdress(UserAddressEntity $userAddress): static
    {
        $this->userAddress = $userAddress;
        return $this;
    }

}