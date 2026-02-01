<?php

namespace App\Domain\Auth;

use App\Domain\Shared\Actor\ActorRole;

class AuthTokenData
{
    public function __construct(
        public readonly string $id,
        public readonly ActorRole $role,
        public readonly TokenPurpose $purpose,
    ){}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role, 
            'purpose' => $this->purpose
        ];
    }
}