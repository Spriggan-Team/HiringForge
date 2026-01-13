<?php


namespace App\Application\Command\Handlers\User;

use Exception;

use App\Api\Responder\ApiResponseBuilder;

use App\Api\DTO\User\CreateUserRequest;
use App\Application\Command\Usecase\User\UserRegister;


use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;



class CreateUserCommandHandler
{

    public function __construct(private UserRegister $register, private ValidatorInterface $validator){}


    public function handle(CreateUserRequest $command): array
    {
        try{
            $errors = $this->validator->validate($command);

            if(count($errors) > 0){
                throw new BadRequestHttpException("Bad fields validation");
            }

            $this->register->execute($command);
            return ApiResponseBuilder::notice("Everything went suceesfully");
        }
        catch(Exception $execption){
            throw $execption;
        }
    }

}