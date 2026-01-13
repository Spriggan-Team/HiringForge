<?php

namespace App\Api\DTO\User;

class UserResponseDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public string $siret,
        public string $password,
    ){}
}