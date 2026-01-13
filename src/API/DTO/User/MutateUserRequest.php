<?php

namespace App\Api\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

class MutateUserRequest
{
    public function __construct(
        #[Assert\Uuid]
        public string $uuid,
        public ?string $email  = null,
        public ?string $password = null,
    ){}
}