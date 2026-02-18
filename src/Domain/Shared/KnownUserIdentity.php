<?php

namespace App\Domain\Sharedp;

use App\Domain\Shared\Account\AccountRole;

class KnownIdentity {
    public function __construct(
        public string $uuid,
        public string $email,
        public string $password,
        public AccountRole $role,
    )
    {}
}
