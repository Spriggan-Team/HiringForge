<?php

namespace App\Application\Command\Handlers\User;

use Exception;

use App\Api\DTO\User\DeleteUserRequest;
use App\Application\Command\Usecase\User\UserEraser;


use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;



class DeleteUserCommandHandler
{
    public function __construct(private UserEraser $eraser, private ValidatorInterface $validator){}

    public function handle(string $command)
    {
        try{
            $this->eraser->execute($command);
        }
        catch(Exception $exception){
            throw $exception;
        }
    }
}