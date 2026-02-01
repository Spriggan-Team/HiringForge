<?php

namespace App\Domain\Auth;

use App\Domain\Shared\Actor\Actor;

interface AuthSecurityServiceInterface
{
    /**
     * This function generate a JWT based on entry data
     * @param array   the playload you want to secure (or encode with jwt)
     * @return string the encoding string
     */
    public function createTokenFor(array $playload, ?TokenPurpose $purpose = null, ?string $time= null): string;
}