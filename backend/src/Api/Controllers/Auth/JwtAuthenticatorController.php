<?php

namespace App\Api\Controllers\Auth;


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
    ){}


    public function supports(Request $request): ?bool
    {
        $authHeader = $request->headers->get('Authorization');
        if(!$authHeader){
            return false;
        }
        return str_starts_with($authHeader, "Bearer ");
    }


    public function authenticate(Request $request): Passport
    {
        $authHeader = $request->headers->get("Authorization");
        if(!$authHeader){
            throw new CustomUserMessageAuthenticationException("You dont have the permission");
        }

        $jwt = substr($authHeader, 7);

        try{
            $playoad = $this->jwtService->decode($jwt);
        }
        catch(\Exception)
        {
            throw new CustomUserMessageAuthenticationException("Invalid token");
        }

        if (!isset($playoad['id'], $playoad['sub'], $playoad['roles'])) {
            throw new CustomUserMessageAuthenticationException("Token not well formed");
        }

        $user = new AuthenticatedPerson(
            $playoad['id'],
            $playoad['sub'],
            $playoad['roles']
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
        return  ApiResponse::error($exception->getMessage())->toJsonResponse();
    }
}