<?php

namespace App\Api\Controllers\Auth;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\AuthentificateAccount;



use Exception;

use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Application\Usecases\Auth\AuthentificateAccountUseCase;
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


class LoginAuthentictorController extends AbstractAuthenticator
{

    public function __construct(
        private AuthentificateAccountUseCase $usecase,
        private JwtAuthentificator $jwtService,
    ){}

    /**
     * This one is called on every request to decide if this authentificator should be used
     * for the request or not;
     */
    public function supports(Request $request): ?bool
    {
        return in_array($request->getPathInfo(), [
            '/user/login',
            '/agent/login',
            '/candidate/login',
        ]) && $request->isMethod('POST');
    }


    public function authenticate(
        Request $request,
    ): Passport
    {
        try
        {
            $body = json_decode($request->getContent(), true);
            $account = new AuthentificateAccount(
                email: $body['email'],
                password: $body['password']
            );

            $role = match ($request->getPathInfo())
            {
                    '/candidate/login' => AccountRole::CANDIDATE->value,
                    '/user/login' => AccountRole::USER->value,
                    default => AccountRole::USER->value,
            };
            
            //---------------Authentificator
            try{
                $personId = $this->usecase->execute($account);
            }
            catch(RessourceNotFound $notFound){
                throw new CustomUserMessageAuthenticationException('Utilisateur introuvable');
            }
            catch(\DomainException $domainException){
                throw new CustomUserMessageAuthenticationException(
                    $domainException->getMessage() ?? "sOmething wrong happened"
                );
            }
            //-------------------------

            $actor = new AuthenticatedPerson(
                $personId,
                sub: $account->email,
                roles: [$role]
            );

            return new SelfValidatingPassport(
                new UserBadge($actor->getUserIdentifier(), fn()=> $actor),
            );
        }
        catch(Exception)
        {
            throw new AuthenticationException("Something wrong happened");
        }
    }


    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?Response
    {
        try{
            /** @var AuthenticatedPerson $user * */
            $user =  $token->getUser();

            $jwt = $this->jwtService->generate([
                'id' => $user->getId(),
                'sub' => $user->getUserIdentifier(), //currently the email
                'roles' => $user->getRoles(),
            ]);

            return ApiResponse::success(
                data: ['token' => $jwt],
                message: "You've successfully been connected to the service !!"
            )->toJsonResponse();
        }
        catch(\Exception $exception){
            return ApiResponse::error(
                message: "Something went wrong",
                throwable: $exception
            )->toJsonResponse();
        }
    }


    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return ApiResponse::error(
                $exception->getMessage() ?? "Something went wrong, please check your information and try log in again!!",
                $exception
            )->toJsonResponse();
    }

}