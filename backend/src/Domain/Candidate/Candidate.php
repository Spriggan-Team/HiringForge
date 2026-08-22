<?php

namespace App\Domain\Candidate;

use App\Domain\File\StaticMedia;
use App\Domain\Shared\Account\Account;
use App\Domain\Shared\Address;
use App\Domain\Shared\EmailAddress;

class Candidate extends Account
{


    /** @var array<int, StaticMedia> $cvs all resume */
    private array $cvs = [];

    private CandidateStatus $status = CandidateStatus::ACTIVE;

    private ?Address $address;
    private int $searchRadius = 10; //default search radius on map


    private function __construct(
        string $id, 
        string $firstName,
        string $lastName,
        EmailAddress $email,
        string $passwordHash,
        ?StaticMedia $image = null,
        array $cvs = [],
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
        $this->cvs = $cvs;

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
        array $cvs = [],
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
            cvs: $cvs,
            address: $address,
            searchRadius: $searchRadius
        );
    }

    //----------------------------
    //   Business access
    //--------------------------



    /** @return array<int, StaticMedia> */
    public function cvs()
    {
        return $this->cvs;
    }


    public function searchRadius(): int
    {
        return $this->searchRadius;
    }

    
    public function address(){
        return $this->address;
    }

    //------------------------------------------
    // - Business change --
    //-----------------------------------------


    public function addCV(?StaticMedia $cv): static
    {
        if ($cv === null) {
            return $this;
        }

        foreach ($this->cvs as $resume) {
            if ($resume->name === $cv->name) {
                throw new \DomainException('Cannot duplicate resume');
            }
        }

        $this->cvs[] = $cv;

        return $this;
    }

    public function removeCv(?StaticMedia $cv = null): static
    {
        if ($cv === null) {
            return $this;
        }
        $this->cvs = array_filter(
            $this->cvs, 
            fn(StaticMedia $resume) => $resume->name !== $cv->name
        );

        return $this;
    }


    public function setSearchRadius(int $searchRadius): static
    {
        $this->searchRadius = $searchRadius;
        return $this;
    }

    public function chanegStatus(CandidateStatus $status){
        $this->status = $status;
    }
}