<?php

namespace App\Domain\Shared;

use App\Domain\Shared\Account\AccountRole;

class KnownIdentity {
    public function __construct(
        public string $uuid,
        public string $email,
        public string $password,
        public AccountRole $accountType, //accountType
    )
    {}
}
