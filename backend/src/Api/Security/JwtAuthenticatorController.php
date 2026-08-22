<?php

namespace App\Api\Security;


use App\Api\Responder\ApiResponse;
use App\Infrastructure\Security\JwtAuthentificator;
use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Domain\ApplicationErrorCode;
use App\Domain\Security\TokenBlacklistRepositoryInterface;


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
        private JwtAuthentificator $jwtService,
        private TokenBlacklistRepositoryInterface $blacklister 
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

        $jwt = trim(substr($authHeader, 7));

        try {
            $token = $this->jwtService->decode($jwt);
            $jti = $token->claims()->get('jti');

            if ($jti && $this->blacklister->isBlacklisted($jti)) {
                throw new CustomUserMessageAuthenticationException("Token has been blacklisted.", code: 401);
            }
            $payload = $token->claims()->get('payload');
        }
        catch (CustomUserMessageAuthenticationException $e) {
            throw $e;
        }
        catch (\DomainException $e) {
            throw new CustomUserMessageAuthenticationException($e->getMessage(), code: 401, previous: $e);
        }
        catch (\Exception $e) {
            throw new CustomUserMessageAuthenticationException("Invalid token.", previous: $e, code: 400);
        }

        // Payload Validation
        if (!isset($payload['id'], $payload['sub'], $payload['roles'])) {
            throw new CustomUserMessageAuthenticationException("Token identity payload is malformed.");
        }

        $user = new AuthenticatedPerson(
            id: $payload['id'],
            sub: $payload['sub'],
            roles: $payload['roles'],
            jti: $jti
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
            message: "Something went wrong",
            statusCode: $exception->getCode(),
            code: $exception->getCode() === 401 ?  ApplicationErrorCode::AUTH_ACCESS_EXPIRED : null
        )->toJsonResponse();
    }
}