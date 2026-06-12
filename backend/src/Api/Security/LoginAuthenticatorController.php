<?php

namespace App\Api\Security;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\Auth\AuthentificateAccount;


use Exception;

use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\Usecases\Auth\AuthentificateAccountUseCase;
use App\Domain\ApplicationErrorCode;
use App\Domain\Exception\RessourceNotFound;
use App\Domain\Shared\Account\AccountRole;
use App\Infrastructure\Security\JwtAuthentificator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;

use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

// use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;



class LoginAuthenticatorController extends AbstractAuthenticator
{
    public function __construct(
        private AuthentificateAccountUseCase $usecase,
        private JwtAuthentificator $jwtService,
    ) {}

    /**
     * This one is called on every request to decide if this authentificator should be used
     * for the request or not;
     */
    public function supports(Request $request): ?bool
    {
        return in_array($request->getPathInfo(), [
            '/api/user/login',
            '/api/agent/login',
            '/api/candidate/login',
        ], true) && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $body = json_decode($request->getContent(), true);
        
        if (!isset($body['email'], $body['password'])) {
            throw new CustomUserMessageAuthenticationException('Données d’identification incomplètes.', [], 400);
        }

        $account = new AuthentificateAccount(
            email: $body['email'],
            password: $body['password']
        );

        $role = match ($request->getPathInfo()) {
            '/candidate/login' => AccountRole::CANDIDATE->value,
            '/agent/login'     => AccountRole::AGENT->value,
            default             => AccountRole::USER->value,
        };

        try {
            $personId = $this->usecase->execute($account);
        }
        catch (RessourceNotFound) {
            throw new CustomUserMessageAuthenticationException('Identifiants invalides.', [], 404);
        }
        catch (\DomainException $e) {
            throw new CustomUserMessageAuthenticationException($e->getMessage(), [], 403);
        }

        $actor = new AuthenticatedPerson(
            $personId,
            sub: $account->email,
            roles: [$role]
        );

        return new SelfValidatingPassport(
            new UserBadge($actor->getUserIdentifier(), fn() => $actor)
        );
    }

    
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        /** @var AuthenticatedPerson $user */
        $user = $token->getUser();

        $jwt = $this->jwtService->generate([
            'id'    => $user->getId(),
            'sub'   => $user->getUserIdentifier(), //-- currently the email
            'roles' => $user->getRoles(),
        ]);

        return ApiResponse::success(
            data: [
                'token' => $jwt,
                "role" => $user->getRoles()[0]
            ],
            message: "Connexion réussie.",
        )->toJsonResponse();
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return ApiResponse::error(
            message: $exception->getMessage() ?: "Identifiants invalides, veuillez réessayer.",
            code: ApplicationErrorCode::INVALID_CREDENTIALS
        )->toJsonResponse();
    }
}
