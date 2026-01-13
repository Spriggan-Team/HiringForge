<?php

namespace App\Application\Command\Handlers\User;

use Exception;


use App\Api\DTO\User\OverwriteUserRequest;
use App\Api\Responder\ApiResponseBuilder;
use App\Application\Command\Usecase\Account\UserOverwritter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;



class OverwriteUserCommandHandler
{
    public function __construct(private UserOverwritter $writter,private ValidatorInterface $validator){}

    public function handle(OverwriteUserRequest $command): array
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