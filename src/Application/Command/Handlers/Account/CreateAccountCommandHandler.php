<?php


namespace App\Application\Command\Handlers\Account;

use Exception;

use App\Api\Responder\ApiResponseBuilder;
use App\Api\DTO\Account\CreateAccountRequest;
use App\Application\Command\Usecase\Account\AccountRegister;


use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class CreateAccountCommandHandler{

    public function __construct(private AccountRegister $register, private ValidatorInterface $validator){}


    public function handle(CreateAccountRequest $command): array
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