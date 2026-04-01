<?php

namespace App\Domain\Shared;
use DomainException;

class PlainPassword
{

    /**
     * This function enforce domain validaty on the password
     * It ensures your password must respect good practics (for instance, indexd by teh CNIL)
     * @throws DomainException    this is throws when expectations are not met
     * @return self
     */
    
    public function __construct(
        public string $plain
    ){
        //CNIL password Rules here
    }

    public function value(): string
    {
        return $this->plain;
    }
}