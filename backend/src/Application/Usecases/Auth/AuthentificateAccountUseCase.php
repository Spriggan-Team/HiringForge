<?php

namespace App\Application\Usecases\Auth;

use App\Application\DTO\Auth\AuthentificateAccount;
use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\PasswordHasherInterface;

class AuthentificateAccountUseCase
{
    public function __construct(
        private PasswordHasherInterface $hasher,
        private AccountRepositoryInterface $accountRepository
    ){}

    /**
     * This one is to authenticate account
     * @throws RessourceNotFound|DomainException  This is an exception that can be throw when something is not correct
     *                          or do not the the buisness logic in the data provided
     * @return string
     */
    public function execute(
        AuthentificateAccount $authentificateAccount
    ):string
    {
        $identity = $this->accountRepository->assertExist(null,  EmailAddress::create($authentificateAccount->email)->value());
        $isPasswordCorrect = $identity && $this->hasher->verify($identity->password, $authentificateAccount->password);

        if(!$isPasswordCorrect){
            throw new \DomainException("The password is not correct");
        }

        return $identity->uuid;
    }
}