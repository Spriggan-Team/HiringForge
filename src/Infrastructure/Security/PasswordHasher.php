<?php

namespace App\Infrastructure\Security;

use App\Domain\Shared\PasswordHasherInterface;

class PasswordHasher implements PasswordHasherInterface
{
    public function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function verify(string $password, string $hash): bool
    {
        throw new \Exception('Not implemented');
    }
}