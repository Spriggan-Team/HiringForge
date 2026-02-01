<?php

namespace App\Infrastructure\Security;

use App\Domain\Auth\TokenPurpose;
use App\Domain\Auth\AuthSecurityServiceInterface;

class AuthSecurityService implements AuthSecurityServiceInterface
{
    public function createTokenFor(array $playload, ?TokenPurpose $purpose = null, ?string $time=null ): string
    {
        return "some token";
    }
}