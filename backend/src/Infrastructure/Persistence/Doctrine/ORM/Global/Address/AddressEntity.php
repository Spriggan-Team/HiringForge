<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\Address;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
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
    private ?string $street = null;

    #[ORM\Column(length: 255)]
    private ?string $postalCode = null;

    #[ORM\Column(length: 255)]
    private ?string $country = null;

    //--------------------
    // Relations
    //---------------------

    #[ORM\OneToOne(
        targetEntity: UserAddressEntity::class,
        mappedBy: 'address'
    )]
    private UserAddressEntity $userAddress;

    #[ORM\OneToOne(
        targetEntity: CandidateEntity::class,
        mappedBy: 'address'
    )]
    private CandidateEntity $candidate;

    //-----------------
    // Constructing...
    //------------------
    
    public static function create(
        string $street,
        string $postalCode,
        string $country,
    ): static
    {
        $address = new self();
        $address->setStreet($street)
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


    public function getCountry(): string{
        return $this->country;
    }

    public function getUserAddress(): UserAddressEntity
    {
        return $this->userAddress;
    }

    public function getCandidate(): CandidateEntity
    {
        return $this->candidate;
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



    public function setUserAddress(UserAddressEntity $userAddress): static
    {
        $this->userAddress = $userAddress;
        return $this;
    }
    

    public function setCandidate(CandidateEntity $candidate):static
    {
        $this->candidate = $candidate;
        return $this;
    }

}