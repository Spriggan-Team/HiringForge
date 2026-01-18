<?php

namespace App\Api\DTO\User;

use App\Domain\Shared\ValueObject\Address;

class UserResponseDTO
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public string $siret,
        public  $address,
    ){}
}