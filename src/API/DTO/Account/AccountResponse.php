<?php

namespace App\Api\DTO\Account;

class AccountResponse
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public string $siret,
        public string $password,
    ){}
}