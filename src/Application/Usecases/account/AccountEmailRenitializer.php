<?php

namespace App\Application\Usecases\account;

use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\PasswordHasherInterface;
use App\Domain\Shared\PlainPassword;


class AccountEmailRenitializer
{
    public function __construct(
        private PasswordHasherInterface $hasher
    ){}

    /**
     * This function enforce special security for 
     * changin email.
     * @throws RessourceNotFound Is thrown when no user is found in the bdd
     * @throws \DomainException  Is thrown when a domain exception is raised; here it is when the provided password doesn't match the user
     */
    public function execute(
        string $oldEmail,
        string $newEmail,
        string $password,
        AccountRepositoryInterface $repository,
    )
    {
        $oldValue =  EmailAddress::create($oldEmail);
        $newValue =  EmailAddress::create($newEmail);
        $plainPassword = new PlainPassword($password);

        $user = $repository->exists(null, $oldValue);
        if($user && $this->hasher->verify($plainPassword->value(), $user->password))
        {
            $repository->changeEmail($newValue->value());
            return;
        }

        throw new \DomainException("Password incorrect");
    }

}