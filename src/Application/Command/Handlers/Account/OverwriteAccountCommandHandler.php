<?php

namespace App\Application\Command\Handlers\Account;

use Exception;
use App\Api\DTO\Account\OverwriteAccountRequest;
use App\Api\Responder\ApiResponseBuilder;
use App\Application\Command\Usecase\Account\AccountOverwritter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class OverwriteAccountCommandHandler
{
    public function __construct(private AccountOverwritter $writter,private ValidatorInterface $validator){}

    public function handle(OverwriteAccountRequest $command): array
    {
        try{
            $errors = $this->validator->validate($command);

            if(count($errors) > 0){
                throw new BadRequestHttpException("Bad Fields validation");
            }

            $this->writter->execute($command);
            return ApiResponseBuilder::notice("Everything went smoothly");
        }
        catch(Exception $exception){
            throw $exception;
        }
    } 
}