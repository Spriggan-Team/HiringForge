<?php

namespace App\Api\Security;


use App\Api\Responder\ApiResponse;
use App\Infrastructure\Security\JwtAuthentificator;
use App\Application\DTO\Auth\AuthenticatedPerson;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;


class JwtAuthenticatorController extends AbstractAuthenticator
{
    public function __construct(
        private JwtAuthentificator $jwtService
    ) {}

    public function supports(Request $request): ?bool
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader) {
            return false;
        }
        
        //-- Allow "Bearer " ou "bearer "
        return str_starts_with(strtolower($authHeader), "bearer ");
    }

    public function authenticate(Request $request): Passport
    {
        $authHeader = $request->headers->get("Authorization");
        if (!$authHeader) {
            throw new CustomUserMessageAuthenticationException("Missing authentication token.");
        }

        //-- jwt extraction
        $jwt = trim(substr($authHeader, 7));

        try {
            $payload = $this->jwtService->decode($jwt);
        } catch (\Exception) {
            throw new CustomUserMessageAuthenticationException("Expired or invalid token.");
        }

        // Payload Validation
        if (!isset($payload['id'], $payload['sub'], $payload['roles'])) {
            throw new CustomUserMessageAuthenticationException("Token identity payload is malformed.");
        }

        $user = new AuthenticatedPerson(
            $payload['id'],
            $payload['sub'],
            $payload['roles']
        );

        return new SelfValidatingPassport(new UserBadge(
            $user->getUserIdentifier(),
            fn() => $user
        ));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return ApiResponse::error(
            message: $exception->getMessage(),
            statusCode: 401 
        )->toJsonResponse();
    }
}