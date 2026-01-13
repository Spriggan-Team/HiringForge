<?php

namespace App\Api\DTO\User;


use Symfony\Component\Validator\Constraints as Assert;

class DeleteUserRequest
{
    public function __construct(
        #[Assert\Uuid]
        public string $uuid
    ){}
}