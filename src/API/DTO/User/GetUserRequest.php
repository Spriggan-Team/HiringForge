<?php

namespace App\Api\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;


class GetUserRequest{
    public function __construct(
        #[Assert\Uuid]
        public string $uuid
    ){}
}