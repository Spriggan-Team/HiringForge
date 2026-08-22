<?php

namespace App\Domain\User;

use DomainException;

class Siret
{

    private function __construct(
      private  string $value
    ){}

    /**
     * Enforce logic buisness
     */
    public static function create(string $value)
    {
        if(!self::isValid($value))
        {
            throw new DomainException("Invalid User Siret"); 
        }
        return new self($value);
    }


    /**
     * WRNING: this function doesn't apply buisness logic
     */
    public static function hydrate(string $value):self
    {
        return new self($value);
    }


    /**
     * This function return the value (int) of the siret
     */
    public function value(): string
    {
        return $this->value;
    }


    /**
     * This is a function that make it possible to compare two different Siret object
     * @param Siret     $other The Siret object you want to compare to the current one
     * @return bool     Tell if the result did go
     */
    public function equals(Siret $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * A representation of the current class' object
     */
    public function __toString(): string
    {
        return $this->value;
    }


    /**
     * This function verify if a string is valid as an siret
     * @param string $siret The string to verify
     * @return bool
     */
    private static function isValid(string $siret): bool
    {
        if (!preg_match('/^\d{14}$/', $siret)) {
            return false;
        }
        return true;
    }

    
    public function siren():string
    {
        return substr($this->value, 0, 8);
    }


    public function nic(): string
    {
        return substr($this->value, 9, 5);
    }
}