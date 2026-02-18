<?php

namespace App\Application\Usecases\account;

use App\Domain\OTP\OTPRepositoryInterface;
use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Domain\Shared\Account\AccountRepositoryInterface;

use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\PlainPassword;

use App\Domain\Shared\PasswordHasherInterface;


class AccountPasswordRenitializer
{
    public function __construct(
        private OTPRepositoryInterface $OTPRepository,
        private PasswordHasherInterface $hasher,
        private AccountRepositoryInterface $repository,
    ){}


    /**
     * This function reset a user's password based on OTP verfication
     * @throws DomainException  this is throwned when something went wrong (A logical error, with a message that can be exposed)
     *                          It is recommended to use error handling structure for managing fallback here
     */
    public function execute(
        string $email,
        string $password,
        string $verificationToken,
    )
    {
        //---idendity checking
        $clearEmail =  EmailAddress::create($email);
        $plainPassword = new PlainPassword($password);

        $identity = $this->repository->exists(null, $clearEmail);

        //---OTP recuperation & verification
        $otp  =  $this->OTPRepository->getLastVerificationTokenWithPurpose(
            $clearEmail->value(),
            AccountFlowPurpose::PASSWORD_RESET
        );

        $isOtpVerified = $otp->verify(
            plainCode: $verificationToken,
            hasher: $this->hasher
        );

        //Throws logic exception when hash verification not succeed
        if(!$isOtpVerified)
        {
            throw new \DomainException("OTP code not correct!!");
        }

        //succedd
        $this->repository->changePassword(
            $identity->email,
            $this->hasher->hash($plainPassword->value())
        );
    }
}