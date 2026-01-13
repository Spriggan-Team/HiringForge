<?php

namespace App\Api\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

class CreateUserRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        public string $password,

        #[Assert\NotBlank]
        public string $siret,
    ){}
}
