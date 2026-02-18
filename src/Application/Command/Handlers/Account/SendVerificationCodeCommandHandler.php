<?php

namespace App\Application\Command\Handlers\Account;



use App\Api\Responder\ApiResponse;

use App\Application\Command\Usecase\Account\VerificationCodeSender;
use App\Domain\Shared\Account\AccountFlowPurpose;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SendVerificationCodeCommandHandler
{
    public function __construct(
        private VerificationCodeSender $verificationCodeSender,
        private ValidatorInterface $validator,
    ){}

    /**
     * 
     * This handler enforce validaion if needed and
     * call for the usecase 
     */
    public function handle(
        string $email,
        AccountFlowPurpose $purpose,
    ): ApiResponse
    {
        try{
            $this->verificationCodeSender->execute(
                email: $email,
                purpose: $purpose,
            );
            return ApiResponse::notice("Please!, check your mail for the verification code");
        }
        catch(\DomainException $domainException){
            return ApiResponse::error($domainException->getMessage() ?? "Something went wrong");
        }
    } 
}