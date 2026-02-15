<?php

namespace App\Application\Command\Handlers\Auth;

use App\Api\Responder\ApiResponseBuilder;

class DeAuthenticateCommandHandler
{
    public function __construct(
    ){}

    public function handle(string $token)
    {
        return ApiResponseBuilder::notice("Everything went somethely"); 
    }
}