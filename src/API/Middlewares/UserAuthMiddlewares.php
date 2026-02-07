<?php

namespace App\Api\Middlewares;

use App\Application\DTO\RequireAuthentification;
use App\Infrastructure\Security\UserGuard;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class UserAuthMiddlewares implements MiddlewareInterface
{
    public function __construct(
        private UserGuard $guard
    ){}

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        if( property_exists($message, "auth") &&
            $message->auth instanceof RequireAuthentification
        ){
            $this->guard->assertAuthorization($message->auth->token);
        }
        return $stack->next()->handle($envelope, $stack);
    }
}