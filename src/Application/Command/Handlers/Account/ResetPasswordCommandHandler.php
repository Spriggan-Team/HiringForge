<?php

namespace App\Application\Command\Handlers\Account;

use Exception;

use App\Api\Responder\ApiResponse;
use App\Application\Command\Utils\AccountRepositoryFactory;
use App\Application\DTO\ChangePassword;
use App\Domain\Shared\Account\AccountRole;
use App\Application\Usecases\account\AccountPasswordRenitializer;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class ResetPasswordCommandHandler
{
    public function __construct(
        private AccountPasswordRenitializer $accountPasswordRenitializer,
        private AccountRepositoryFactory $accountRepositoryfactory,
        private ValidatorInterface $validator
    ){}

    /**
     * This is an handler.
     * A function that enforce major techinical validation and call for the usecase
     */
    public function handle(ChangePassword $changePassword, AccountRole $role): ApiResponse
    {
        try{
            $errors = $this->validator->validate($changePassword);
            if(count($errors) > 0)
            {
                throw new  BadRequestHttpException();
            }

            $repository =  $this->accountRepositoryfactory->create($role->value);

            //Call for the usecase
            $this->accountPasswordRenitializer->execute(
                $changePassword->email,
                $changePassword->password,
                $changePassword->verificationCode,
                $repository
            );
            return ApiResponse::notice("Everything went smoothly");
        }
        catch(Exception $exception){
            throw $exception;
        }
    }
    
}