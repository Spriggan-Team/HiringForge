<?php

namespace App\Application\Command\Handlers\Account;

use Exception;

use App\Api\DTO\Account\MutateAccountRequest;
use App\Api\Responder\ApiResponseBuilder;
use App\Application\Command\Usecase\Account\AccountModifier;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MutateAccountCommandHandler
{
    public function __construct(private AccountModifier $modifier, private ValidatorInterface $validator){}

    public function handle(MutateAccountRequest $command): array
    {
        try{
            $errors = $this->validator->validate($command);

            if(count($errors) > 0){
                throw new BadRequestHttpException("Bad fields validation");
            }

            $this->modifier->execute($command);
            return ApiResponseBuilder::notice("Everything went smoothly");
        }
        catch(Exception $exception){
            throw $exception;
        }
    }
}