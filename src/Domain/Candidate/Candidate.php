<?php

namespace App\Domain\Candidate;

use App\Domain\File\StaticMedia;
use App\Domain\Shared\Actor\Actor;

class Candidate implements Actor
{
    private CandidateId $id;

    private string $firstName;
    private string $lastName;

    private string $email;
    private string $passwordHash;

    private ?StaticMedia $image = null;
    private StaticMedia $cv;


    private function __construct(
        CandidateId $id, 
        string $firstName,
        string $lastName,
        string $email,
        string $passwordHash,
        ?StaticMedia $image = null,
        StaticMedia $cv,
    ){
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->image = $image;
        $this->cv = $cv;
    }

    public static function create(
        CandidateId $id, 
        string $firstName,
        string $lastName,
        string $email,
        string $passwordHash,
        ?StaticMedia $image = null,
        StaticMedia $cv,
    )
    {
        return new self(
            $id,
            $firstName,
            $lastName,
            $email,
            $passwordHash,
            $image,
            $cv
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
        return $this->email;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function image():StaticMedia
    {
        return $this->image;
    }

    public function cv(): StaticMedia
    {
        return $this->cv;
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

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function setImage(StaticMedia $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function setCv(StaticMedia $cv):static
    {
        $this->cv =$cv;
        return $this;
    }
}