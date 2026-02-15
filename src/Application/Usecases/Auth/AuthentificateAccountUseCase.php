<?php

namespace App\Application\Usecases\Auth;

use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\PasswordHasherInterface;

class AuthentificateAccountUseCase
{
    public function __construct(
        private PasswordHasherInterface $hasher
    ){}

    /**
     * This one is to authenticate account
     * @throws DomainException  This is an exception that can be throw when something is not correct
     *                          or do not the the buisness logic in the data provided
     * @return string
     */
    public function execute(
        string $email,
        string $password,
        AccountRepositoryInterface $repository
    ):string
    {
        $identity = $repository->exists(null,  EmailAddress::create($email));
        $isPasswordCorrect = $identity && $this->hasher->verify($identity->password, $password);

        if(!$isPasswordCorrect){
            throw new \DomainException("The password is not correct");
        }

        return $identity->uuid;
    }
}