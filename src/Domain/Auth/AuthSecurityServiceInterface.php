<?php

namespace App\Domain\Auth;

use App\Domain\Shared\Actor\Actor;

/**
 * Just a proposition for any token service,
 * It should not directly use by any use case.
 */
interface AuthSecurityServiceInterface
{
    /**
     * This function generate a JWT based on entry data
     * @param array   the playload you want to secure (or encode with jwt)
     * @return string the encoding string
     */
    public function createTokenFor(array $playload, ?TokenPurpose $purpose = null, ?string $time= null): string;
}