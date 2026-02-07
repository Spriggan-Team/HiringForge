<?php

namespace App\Domain\Sharedp;




class KnownIdentity {
    public function __construct(
        public string $uuid,
        public string $email,
        public string $passwod,
    )
    {}
}
