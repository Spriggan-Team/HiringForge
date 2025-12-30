<?php

namespace App\Api\DTO\Account;

use Symfony\Component\Validator\Constraints as Assert;


class GetAccountRequest{
    public function __construct(
        #[Assert\Uuid]
        public string $uuid
    ){}
}