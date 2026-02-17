<?php

namespace App\Application\Command\Handlers\Account;



use App\Api\Responder\ApiResponse;
use App\Domain\Shared\Account\AccountRole;

use App\Application\Command\Utils\AccountRepositoryFactory;
use App\Application\Command\Usecase\Account\VerificationCodeSender;
use App\Domain\Shared\Account\AccountFlowPurpose;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class SendVerificationCodeCommandHandler
{
    public function __construct(
        private AccountRepositoryFactory $accountRepositoryFactory,
        private VerificationCodeSender $verificationCodeSender,
        private ValidatorInterface $validator,
    ){}

    /**
     * This handler enforce validaion if needed and
     * call for the usecase 
     */
    public function handle(
        string $email,
        AccountFlowPurpose $purpose,
        AccountRole $role,
    ): ApiResponse
    {
        try{
            $this->verificationCodeSender->execute(
                email: $email,
                purpose: $purpose,
                repository: $this->accountRepositoryFactory->create($role->value)
            );
            return ApiResponse::notice("Please!, check your mail for the verification code");
        }
        catch(\DomainException $domainException){
            return ApiResponse::error($domainException->getMessage() ?? "Something went wrong");
        }
    } 
}