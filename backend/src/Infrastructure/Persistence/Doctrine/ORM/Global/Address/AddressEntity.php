<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Address;



use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: "address")]
class AddressEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $street = null;

    #[ORM\Column(length: 255)]
    private ?string $postalCode = null;  
    
    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column(length: 255)]
    private ?string $country = null;


    //-----------------
    // Constructing...
    //-----------------
    
    public static function create(
        string $city,
        string $street,
        string $postalCode,
        string $country,
    ): static
    {
        $address = new self();
        $address->setStreet($street)
                ->setCity($city)
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

    public function getCity(): string{
        return $this->city;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCountry(): string
    {
        return $this->country;
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

    public function setCountry(string $country): static
    {
        $this->country = $country;
        return $this;
    }

    public function setCity(string $city): static
    {
        $this->city  = $city;
        return $this;
    }
}