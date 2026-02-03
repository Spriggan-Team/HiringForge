<?php

namespace App\Application\Command\Handlers\User;

use Exception;

use App\Api\Responder\ApiResponseBuilder;

use App\Application\Command\Usecase\User\UserResetPassword;
use App\Application\DTO\ChangePassword;
use Symfony\Component\Validator\Validator\ValidatorInterface;



class ResetPasswordCommandHandler
{
    public function __construct(
        private UserResetPassword $modifier,
        private ValidatorInterface $validator
    ){}

    public function handle(ChangePassword $changePassword): array
    {
        try{
            $this->modifier->execute(
                $changePassword->uuid,
                $changePassword->password,
                $changePassword->verificationCode
            );
            return ApiResponseBuilder::notice("Everything went smoothly");
        }
        catch(Exception $exception){
            throw $exception;
        }
    }
}