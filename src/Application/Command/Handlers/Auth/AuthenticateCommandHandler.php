<?php

namespace App\Application\Command\Handlers\Auth;

use App\Domain\Exception\RessourceNotFound;
use App\Application\DTO\AuthentificateAccount;

use App\Application\Command\Utils\AccountRepositoryFactory;
use App\Application\Usecases\Auth\AuthentificateAccountUseCase;


use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class AuthenticateCommandHandler
{
    public function __construct(
        private ValidatorInterface $validator,
        private AuthentificateAccountUseCase $authentificator,
        private AccountRepositoryFactory $accountRepositoryFactory
    )
    {} 

    public function handle(AuthentificateAccount $account, array $roles): string
    {
        try{
            $errors = $this->validator->validate($account);
            if(count($errors) > 0){
                throw new BadRequestHttpException('Email et mot de passe requis');
            }

            $repo = $this->accountRepositoryFactory->create($roles[0] ?? "");
            
            $personId = $this->authentificator->execute(
                email: $account->email,
                password: $account->password,
                repository: $repo
            );

            return $personId;
        }
        catch(BadRequestHttpException $badRequest)
        {
            throw new CustomUserMessageAuthenticationException('Email & password are required');
        }
        catch(RessourceNotFound $notFound){
            throw new CustomUserMessageAuthenticationException('Utilisateur introuvable');
        }
        catch(\DomainException $domainException){
            throw new CustomUserMessageAuthenticationException(
                $domainException->getMessage() ?? "sOmething wrong happened"
            );
        }
    }
}