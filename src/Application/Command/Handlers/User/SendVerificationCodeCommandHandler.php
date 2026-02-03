<?php

namespace App\Application\Command\Handlers\User;

use Exception;


use App\Api\Responder\ApiResponseBuilder;
use App\Application\Command\Usecase\Account\VerificationCodeSender;
use Symfony\Component\Validator\Validator\ValidatorInterface;



class SendVerificationCodeCommandHandler
{
    public function __construct(
        private VerificationCodeSender $verificationCodeSender,
        private ValidatorInterface $validator
    ){}

    public function handle(string $email): array
    {
        try{
            $this->verificationCodeSender->execute($email);
            return ApiResponseBuilder::notice("Please!, check your mail for the verification code");
        }
        catch(Exception $exception){
            throw $exception;
        }
    } 
}