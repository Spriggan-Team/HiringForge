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
     * This function change the value of the siret while enforcing
     * mandatory control
     * @param Siret $other      The new value object to use to make change
     * @throws \DomainException It is raised when the control failed
     * @return void             If the function make its way here without any exception thrown,
     *                          then everything went smoothly
     */
    public function change(Siret $other)
    {
        if(
            $this->siren() === $other->siren()
        ){
            $this->value = $other->value();
            return;
        }
        throw new DomainException("You must meet the conditions of validation to change a siret");
    }

    /**
     * This function verify if a string is valid as an siret
     * @param string $siret The string to verify
     * @return bool
     */
    private function isValid(string $siret): bool
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