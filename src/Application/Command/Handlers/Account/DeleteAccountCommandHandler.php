<?php

namespace App\Application\Command\Handlers\Account;

use Exception;

use App\Api\DTO\Account\DeleteAccountRequest;
use App\Application\Command\Usecase\Account\AccountEraser;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class DeleteAccountCommandHandler
{
    public function __construct(private AccountEraser $eraser, private ValidatorInterface $validator){}

    public function handle(DeleteAccountRequest $command)
    {
        try{
            $errors = $this->validator->validate($command);
            if(count($errors) > 0)
                throw new BadRequestHttpException();
            
            $this->eraser->execute($command);
        }
        catch(Exception $exception){
            throw $exception;
        }
    }
}