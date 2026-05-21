<?php

namespace App\Domain\Candidate;

use App\Domain\File\StaticMedia;
use App\Domain\Shared\Account\Account;
use App\Domain\Shared\Address;
use App\Domain\Shared\EmailAddress;

class Candidate implements Account
{
    private CandidateId $id;

    private string $firstName;
    private string $lastName;

    private EmailAddress $email;
    private string $passwordHash;

    private ?StaticMedia $image = null;
    private ?StaticMedia $cv;

    private ?Address $address;
    private int $searchRadius = 10; //default search radius on map

    private ?string $description = null;

    private function __construct(
        CandidateId $id, 
        string $firstName,
        string $lastName,
        EmailAddress $email,
        string $passwordHash,
        ?StaticMedia $image,
        ?StaticMedia $cv,
        ?Address $address,
        ?int $searchRadius,
    ){
        $this->id = $id;

        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;

        $this->passwordHash = $passwordHash;
        $this->image = $image;
        $this->cv = $cv;

        $this->address = $address;
        if($searchRadius){
            $this->searchRadius = $searchRadius;
        }
    }

    public static function create(
        CandidateId $id, 
        string $firstName,
        string $lastName,
        EmailAddress $email,
        string $passwordHash,
        ?StaticMedia $image = null,
        ?StaticMedia $cv = null,
        ?Address $address = null,
        ?int $searchRadius = null,
    ): self
    {
        return new self(
            $id,
            $firstName,
            $lastName,
            $email,
            $passwordHash,
            $image,
            $cv,
            $address,
            $searchRadius
        );
    }

    //----------------------------
    //   Business access
    //--------------------------

    public function id():string
    {
        return $this->id->value();
    }

    public function firstName() : string 
    {
        return $this->firstName;    
    }

    public function lastName(): string
    {
        return $this->lastName;
    }

    public function email(): string
    {
        return $this->email->value();
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function image():StaticMedia
    {
        return $this->image;
    }

    public function cv(): ?StaticMedia
    {
        return $this->cv;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function address(): ?Address
    {
        return $this->address;
    }

    public function searchRadius(): int
    {
        return $this->searchRadius;
    }
    //------------------------------------------
    // - Business change --
    //-----------------------------------------

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName(string $lastName):static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function setEmail(EmailAddress $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function setImage(?StaticMedia $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function removeImage():static
    {
        $this->image = null;
        return $this;
    }

    public function setCv(?StaticMedia $cv):static
    {
        $this->cv =$cv;
        return $this;
    }

    public function removeCv():static
    {
        $this->cv = null;
        return $this;
    }

    public function setDescription(?string $description):static
    {
        $this->description = $description;
        return $this;
    }

    public function setAddress(?Address $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function setSearchRadius(int $searchRadius): static
    {
        $this->searchRadius = $searchRadius;
        return $this;
    }
}