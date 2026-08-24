<?php

namespace App\Domain\Shared\Account;

use App\Domain\File\StaticMedia;
use App\Domain\Shared\EmailAddress;


abstract class Account
{
    protected  string $id;
    protected  string  $lastName;
    protected  string $firstName;

    protected EmailAddress  $email;
    protected string  $passwordHash;
    protected  ?StaticMedia $image = null;
    
    protected ?string $description = null;


    public function __construct(
        string $firstName,
        string $lastName,
        EmailAddress $email,
        string $passwordHash,
        ?string $id = null,
        ?string $description = null,
        ?StaticMedia $image = null,
    ) {
        $this->id = $id;
        $this->firstName  = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->image = $image;
        $this->description = $description;
    }


    //----------------------------
    //   Business access
    //--------------------------

    public function id(): string 
    {
        return $this->id;
    }

    public function lastName(): string {
        return $this->lastName;
    }

    public function firstName(): string{
        return $this->firstName;
    }

    public function email(): string 
    { 
        return $this->email->value();
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    /**
     * @return ?StaticMedia images
     */
    public function image(): ?StaticMedia
    { 
        return $this->image;
    }


    public function description() : ?string
    {
        return $this->description;    
    }

        

    //------------------------------------------
    // - Business change --
    //-----------------------------------------

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function rename(string $firstName, string $lastName): static
    {
        if ($this->firstName != $firstName) {
            $this->firstName = $firstName;
        }

        if ($this->lastName != $lastName) {
            $this->lastName = $lastName;
        }

        return $this;
    }

    public function setEmail(EmailAddress $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function changeEmail(?EmailAddress $email)
    {
        if($email && $this->email->value() != $email->value()){
            $this->email = $email;
        }
        return $this;
    }


    public function setPassword(
        string $passwordHash, 
    ): static
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    public function addImage(StaticMedia $image):static
    {
        $image->mustBe(sizeLimitation: 12500000);
        $this->image = $image;
        return $this;
    }

    public function removeImage(): static
    {
        $this->image = null;
        return $this;
    }


    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }
}