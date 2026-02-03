<?php

namespace App\Domain\User;




class KnownUserIdentity {
    public function __construct(
        public string $uuid,
        public string $email,
        public string $passwod,
    )
    {}
}
