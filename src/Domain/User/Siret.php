<?php

namespace App\Domain\User;

use DomainException;

class Siret
{
    private string $value;
    public function __construct(string $value)
    {
        if($this->isValid($value)){
            $this->value = $value;
            return;
        }
        throw new DomainException("Invalid User Siret"); 
    }

    /**
     * This function return the value (int) of the siret
     */
    public function value(): string
    {
        return $this->value;
    }


    public function equals(Siret $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * This function verify if a string is valid for create a Siret
     * @return bool
     */
    private function isValid(string $siret): bool
    {
        if (!preg_match('/^\d{14}$/', $siret)) {
            return false;
        }

        return true;
    }
}