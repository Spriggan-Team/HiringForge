<?php

namespace App\Application\Command\Handlers\Auth;

use App\Api\Responder\ApiResponseBuilder;
use App\Application\Command\Usecase\Auth\DeAuthentificator;
use App\Infrastructure\Security\AuthSecurityService;

class DeAuthenticateCommandHandler
{
    public function __construct(
        private DeAuthentificator $deAuthentificator
    ){}

    public function handle(string $token)
    {
        $this->deAuthentificator->execute($token);
        return ApiResponseBuilder::notice("Everything went somethely"); 
    }
}