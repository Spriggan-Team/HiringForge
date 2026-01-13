<?php

namespace App\Application\Command\Handlers\User;

use Exception;

use App\Api\Responder\ApiResponseBuilder;

use App\Api\DTO\User\MutateUserRequest;
use App\Application\Command\Usecase\User\UserModifier;


use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;



class MutateUserCommandHandler
{
    public function __construct(private UserModifier $modifier, private ValidatorInterface $validator){}

    public function handle(MutateUserRequest $command): array
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