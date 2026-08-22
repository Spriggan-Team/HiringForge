<?php

namespace App\Infrastructure\Security;

use App\Domain\Shared\PasswordHasherInterface;

class PasswordHasher implements PasswordHasherInterface
{
    public function hash(string $password, ?string $algorithm = null): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function verify(string $password, string $hash, ?string $algorithm = null): bool
    {
        return password_verify($password, $hash);
    }
}