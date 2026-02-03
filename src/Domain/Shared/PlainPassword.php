<?php

namespace App\Domain\Shared;
use DomainException;

class PlainPassword
{

    /**
     * @throws DomainException    This function
     * @return self
     */
    
    public function __construct(
        public string $plain
    ){

    }

    public function value(): string
    {
        return $this->plain;
    }
}