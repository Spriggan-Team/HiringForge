<?php

namespace App\Application\Usecases\Account;

use App\Api\Responder\ApiResponse;
use App\Domain\OTP\OTP;
use App\Domain\Shared\EmailAddress;

use App\Domain\Email\EmailCategory;
use App\Domain\Email\EmailMessage;
use App\Domain\Shared\Account\AccountFlowPurpose;

use App\Domain\OTP\OTPRepositoryInterface;
use App\Domain\Email\EmailServicesInterface;
use App\Domain\Exception\EmailAlreadyRegistered;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Shared\PasswordHasherInterface;
use App\Domain\Shared\Account\AccountRepositoryInterface;

use function PHPSTORM_META\type;

class VerificationCodeSender
{
    public function __construct(
        private PasswordHasherInterface $hasher,
        private OTPRepositoryInterface $OTPRepository,
        private EmailServicesInterface $emailServices,
        private AccountRepositoryInterface $repository,
    ){}


    /**
     * @param string $email - the email where to send the verification code
     * @param AccountFlowPurpose $purpose - why the current code is generated
     */
    public function execute(string $email, AccountFlowPurpose $purpose): void
    {
        $clearEmail = EmailAddress::create($email);
        $emailString = $clearEmail->value();

        //-- user elligibility
        if ($purpose === AccountFlowPurpose::SIGN_UP) {
            $userExists = $this->repository->exists(email: $emailString); 
            if ($userExists) {
                throw new EmailAlreadyRegistered("This account already exist");
            }
        } 
        else {
            $this->repository->assertExist(email: $emailString);    
        }
        
        $emailMessage = EmailMessage::create(
            purpose: $purpose,
            title: "Code de vérification",
            description: "This is a verification for you to confirm your identity",
        );
        
        // --- Verify if an existing active token is stored in the BDD
        $lastOtp = null;
        try{
            $lastOtp = $this->OTPRepository->getLastVerificationTokenWithPurpose(
                email: $email,
                purpose: $purpose
            );
        }
        catch(ResourceNotFoundException){}


        // -- Generate warning if OTP is still active (Il n'est PAS expiré)
        if ($lastOtp && !$lastOtp->isExpired()) { 
            $remainingSeconds = $lastOtp->getRemainingSeconds();

            if ($remainingSeconds >= 60) {
                $value = ceil($remainingSeconds / 60);
                $unit = $value > 1 ? 'minutes' : 'minute';
            } else {
                $value = $remainingSeconds;
                $unit = $value > 1 ? 'seconds' : 'second';
            }

            $emailMessage->description =
                "The last OTP verification code sent to you is still active. "
                . "Please try it first or wait approximately {$value} {$unit} before requesting a new one.";

            $emailMessage->type = EmailCategory::WARNING;

            $this->emailServices->sendTo(
                receiver: $emailString,
                emailMessage: $emailMessage 
            );

            return; 
        }

        
        // --- Create a new OTP code and save it
        $otp = OTP::create(
            hasher: $this->hasher,
            purpose: $purpose
        );
        $this->OTPRepository->save($emailString, $otp);


        // --- Send the plain text code via email, NOT the hash
        $emailMessage->code = $otp->plainCode;
        $emailMessage->type = EmailCategory::DEFAULT;
        
        $this->emailServices->sendTo(
            receiver: $emailString,
            emailMessage: $emailMessage,
        );
    }
}