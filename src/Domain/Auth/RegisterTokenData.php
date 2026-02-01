<?php

namespace App\Domain\Auth;

use App\Domain\Shared\Actor\ActorRole;

class RegisterTokenData{
        public function __construct(
        public readonly string $id,
        public readonly TokenPurpose $purpose,
    ){}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'purpose' => $this->purpose
        ];
    }
}