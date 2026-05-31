<?php

namespace App\Application\Usecases\Account;

use App\Domain\Email\EmailCategory;
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

    /**
     * @var string $email - the email where to sent the verification code
     * @var AccountFlowPurpose $purpose - why the current code is generated
     */
    public function execute(
        string $email,
        AccountFlowPurpose $purpose,
    ): void
    {
        $clearEmail = EmailAddress::create($email);

        if($purpose !== AccountFlowPurpose::SIGN_UP){
            $this->repository->exists(uuid: null, email: $clearEmail); //-- trigger excption if user does not exist
        }
        
        $emailMessage =  EmailMessage::create(
            purpose: $purpose,
            title: "Code verrification",
            id: \Ramsey\Uuid\Uuid::uuid4()->toString(),
            description: "This is a verification for your to confirm your identity",
        );
        
        //--- Verify if an existing token validation code is not stored in the bdd
        $lastOtp = $this->OTPRepository->getLastVerificationTokenWithPurpose(
            email: $email,
            purpose: $purpose
        );

        //-- Generate warning
        if($lastOtp && !$lastOtp->isExpired())
        {
            $remainingSeconds = $lastOtp->getRemainingSeconds();

            if ($remainingSeconds >= 60) {
                $value = ceil($remainingSeconds / 60);
                $unit = $value > 1 ? 'minutes' : 'minute';
            }
            else {
                $value = $remainingSeconds;
                $unit = $value > 1 ? 'seconds' : 'second';
            }

            $emailMessage->description =
                "The last OTP verification code sent to you is still active. "
                . "Please try it first or wait approximately {$value} {$unit} before requesting a new one.";

            $emailMessage->type = EmailCategory::WARNING;

            $this->emailServices->sendTo(
                receiver: $clearEmail->value(),
                emailMessage: $emailMessage 
            );

            return;
        }

        //--- Create a new otp code and send it to the user
        $otp =  OTP::create(
            hasher: $this->hasher,
            purpose: $purpose
        );
        $this->OTPRepository->save($clearEmail->value() ,$otp);

        
        $emailMessage->code = $otp->hashCode;
        $this->emailServices->sendTo(
            receiver: $clearEmail->value(),
            emailMessage: $emailMessage
        );
    }
}