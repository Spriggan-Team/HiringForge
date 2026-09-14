<?php

namespace App\Domain\Shared;

class EmailAddress
{
    /**
     * This is a value object for email object.
     * @throws \DomainException      Il s'agit d'une exception lancé des erreurs de validation des contraintes metiers
     */
    private function __construct(
        public string $email,
    ){}

    /**
     * It is responsible to enforce buisness logic
     */
    public static function create(string $email): self
    {
        //Buisness logic 
        //TODO: reject temp email
        return new self($email);
    }

    /**
     * !!WARNING: Use with cautious
     * This function allow us to create a email addres without domain verification/validation;
     */
    public static function hydrate(string $email): self
    {
        return new self($email);
    }

    public function value():string
    {
        return $this->email;
    }
}