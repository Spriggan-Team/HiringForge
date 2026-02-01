<?php

namespace App\Domain\Shared;

interface PasswordHasherInterface
{
    /**
     * This function is used to hash a password
     * @param string $password The password to hash
     */
    public function hash(string $password): string;

    /**
     * This function test/compare passord
     * @param string $password   This the password that should be verify
     * @param string $hash       This should be the hash password which correspond to what is stored in the bdd (A sorta nomalized password)
     */
    public function verify(string $password, string $hash): bool;
}