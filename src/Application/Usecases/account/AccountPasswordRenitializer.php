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
        private PasswordHasherInterface $hasher
    ){}


    /**
     * This function reset a user's password based on OTP verfication
     * @throws DomainException  this is throwned when something went wrong (A logical error, with a message that can be exposed)
     *                          It is recommended to use error handling structure for managing fallback here
     */
    public function execute(
        string $email,
        string $password,
        string $verificationCode,
        AccountRepositoryInterface $repository,
    )
    {
        //---idendity checking
        $address =  EmailAddress::create($email);
        $plainPassword = new PlainPassword($password);

        $identity = $repository->exists(null, $address);

        //---OTP recuperation & verification
        $otp  =  $this->OTPRepository->getLastVerificationTokenWithPurpose(
            $address->value(),
            AccountFlowPurpose::PASSWORD_RESET
        );

        $isOtpVerified = $otp->verify(
            plainCode: $verificationCode,
            hasher: $this->hasher
        );

        //Throws logic exception when hash verification not succeed
        if(!$isOtpVerified)
        {
            throw new \DomainException("OTP code not correct!!");
        }

        //succedd
        $repository->changePassword(
            $identity->email,
            $this->hasher->hash($plainPassword->value())
        );
    }
}