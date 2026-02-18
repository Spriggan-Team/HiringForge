<?php

namespace App\Application\Usecases\account;

use App\Domain\OTP\OTPRepositoryInterface;
use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\PasswordHasherInterface;
use App\Domain\Shared\PlainPassword;


class AccountEmailRenitializer
{
    public function __construct(
        private PasswordHasherInterface $hasher,
        private AccountRepositoryInterface $repository,
        private OTPRepositoryInterface $OTPRepository,
    ){}

    /**
     * This function enforce special security for 
     * changin email. It allow an user/account to modify its email knowing its credentials informations and with 
     * an OTP verifictaion code
     * @throws RessourceNotFound Is thrown when no user is found in the bdd
     * @throws \DomainException  Is thrown when a domain exception is raised; here it is when the provided password doesn't match the user
     */
    public function execute(
        string $oldEmail,
        string $newEmail,
        string $password,
        string $verificationToken
    )
    {
        $clearOldEmail =  EmailAddress::create($oldEmail);
        $clearNewEmail =  EmailAddress::create($newEmail);
        $plainPassword = new PlainPassword($password);

        $user = $this->repository->exists(null, $clearOldEmail);

        if($user && $this->hasher->verify($plainPassword->value(), $user->password))
        {
            $otp = $this->OTPRepository->getLastVerificationTokenWithPurpose(
                email: $clearOldEmail->value(),
                purpose: AccountFlowPurpose::EMAIL_CHANGE
            );
            $otp->verify(
                plainCode: $verificationToken,
                hasher: $this->hasher
            );

            $this->repository->changeEmail($clearNewEmail->value());
            return;
        }

        throw new \DomainException("Password incorrect");
    }

}