<?php

namespace App\Application\DTO;

/**
 * It is only used juste as a signal to enforce authentification requirements
 */
class RequireAuthentification
{
    public function __construct(
        public string $token,
    ){}
}