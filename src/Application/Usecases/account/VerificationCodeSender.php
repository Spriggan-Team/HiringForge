<?php


namespace App\Application\Command\Usecase\Account;

use App\Domain\OTP\OTP;
use App\Domain\Shared\EmailAddress;
use App\Domain\Email\EmailMessage;


use App\Domain\Shared\Account\AccountFlowPurpose;

use App\Domain\OTP\OTPRepositoryInterface;
use App\Domain\Email\EmailServicesInterface;
use App\Domain\Shared\PasswordHasherInterface;
use App\Domain\Shared\Account\AccountRepositoryInterface;


class VerificationCodeSender
{
    public function __construct(
        private PasswordHasherInterface $hasher,
        private OTPRepositoryInterface $OTPRepository,
        private EmailServicesInterface  $emailServices,
        private AccountRepositoryInterface $repository,
    ){}

    public function execute(
        string $email,
        AccountFlowPurpose $purpose,
    ): void
    {
        $clearEmail = EmailAddress::create($email);
        $identity = $this->repository->exists(uuid: null, email: $clearEmail );

        
        $emailMessage =  EmailMessage::create(
            title: "",
            purpose: $purpose,
            description: "This is a verification for your to confirm your identity",
        );
        
        //---Verify if an existing token validation code is not stored in the bdd
        $lastOtp = $this->OTPRepository->getLastVerificationTokenWithPurpose(
            email: $email,
            purpose: $purpose
        );

        if($lastOtp && !$lastOtp->isExpired())
        {
            $emailMessage->code = $lastOtp->hashCode;
            $this->emailServices->sendTo(
                receiver: $identity->email,
                document: $this->emailServices->prepareEmail($emailMessage)
            );
            return;
        }

        //---Create a new otp code and send it to the user
        $otp =  OTP::create(
            hasher: $this->hasher,
            purpose: $purpose
        );
        $this->OTPRepository->save($clearEmail->value() ,$otp);

        $emailMessage->code = $otp->hashCode;
        $this->emailServices->sendTo(
            receiver: $identity->email,
            document: $this->emailServices->prepareEmail($emailMessage)
        );
    }
}