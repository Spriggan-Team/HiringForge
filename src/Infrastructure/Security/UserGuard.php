<?php

namespace App\Infrastructure\Security;

use App\Domain\Auth\AuthTokenData;

class UserGuard
{
    /**
     * This function is a must to validate user authentification.
     * Here it is done through token validation
     * @throws UnauthorizedAction   This exception should be returned when
     * @return string               The id of an user
     */
    public function assertAuthorization(): string
    {
        throw new \Exception('UserGuard Not implemented');
    }
}