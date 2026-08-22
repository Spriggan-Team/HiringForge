<?php

namespace App\Application\DTO;

/**
 * It is only used juste as a signal to enforce authentification requirements
 */
class RequireAuthentification
{
    public function __construct(
        public string $token,
        public ?string $actorId=null      //can represents any actor of the application
    ){}
}