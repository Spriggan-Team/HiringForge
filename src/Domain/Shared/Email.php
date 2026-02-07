<?php

namespace App\Domain\Shared;

class EmailAddress
{
    /**
     * This is a value object for email object.
     * It is respnsible to ensure data domain validation for email.
     * @throws DomainException      Il s'agit d'une exception lancé des erreurs de validation des contraintes metiers
     */
    public function __construct(
        public string $email,
    ){}

    public function value():string
    {
        return $this->email;
    }
}