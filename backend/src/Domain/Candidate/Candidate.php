<?php

namespace App\Domain\Candidate;

use App\Domain\File\StaticMedia;
use App\Domain\Shared\Account\Account;
use App\Domain\Shared\Address;
use App\Domain\Shared\EmailAddress;

class Candidate extends Account
{


    private ?StaticMedia $cv;

    private ?Address $address;
    private int $searchRadius = 10; //default search radius on map


    private function __construct(
        string $id, 
        string $firstName,
        string $lastName,
        EmailAddress $email,
        string $passwordHash,
        ?StaticMedia $image = null,
        ?StaticMedia $cv = null,
        ?Address $address = null,
        ?int $searchRadius = null,
        ?string $description = null
    ){
        $this->id = $id ?? CandidateId::create()->value();

        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->description = $description;

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
        string $firstName,
        string $lastName,
        EmailAddress $email,
        string $passwordHash,
        ?string $id = null, 
        ?StaticMedia $image = null,
        ?StaticMedia $cv = null,
        ?Address $address = null,
        ?int $searchRadius = null,
    ): self
    {
        return new self(
            id: $id,
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
            passwordHash: $passwordHash,
            image: $image,
            cv: $cv,
            address: $address,
            searchRadius: $searchRadius
        );
    }

    //----------------------------
    //   Business access
    //--------------------------



    public function cv(): ?StaticMedia
    {
        return $this->cv;
    }


    public function searchRadius(): int
    {
        return $this->searchRadius;
    }
    //------------------------------------------
    // - Business change --
    //-----------------------------------------


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


    public function setSearchRadius(int $searchRadius): static
    {
        $this->searchRadius = $searchRadius;
        return $this;
    }
}