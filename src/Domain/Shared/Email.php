<?php

namespace App\Domain\Shared;

class EmailAddress
{
    public function __construct(
        public string $email,
    ){}

    public function value():string
    {
        return $this->email;
    }
}