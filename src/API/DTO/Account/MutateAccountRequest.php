<?php

namespace App\Api\DTO\Account;

use Symfony\Component\Validator\Constraints as Assert;

class MutateAccountRequest
{
    public function __construct(
        #[Assert\Uuid]
        public string $uuid,
        public ?string $email  = null,
        public ?string $password = null,
    ){}
}