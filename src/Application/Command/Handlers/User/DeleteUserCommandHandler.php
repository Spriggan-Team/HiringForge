<?php

namespace App\Application\Command\Handlers\User;

use Exception;

use App\Api\Responder\ApiResponse;
use App\Application\Command\Handlers\User\UserEraser;
use App\Domain\Shared\Account\AccountRole;

use Symfony\Component\Validator\Validator\ValidatorInterface;



class DeleteUserCommandHandler
{
    public function __construct(
        private UserEraser $eraser,
        private ValidatorInterface $validator
    ){}

    public function handle(string $userId, AccountRole $role): ApiResponse
    {
        try{
            $this->eraser->execute(
                userId: $userId,
            );
            return ApiResponse::notice("Everything went successfully");
        }
        catch(Exception $exception){
            throw $exception;
        }
    }
}