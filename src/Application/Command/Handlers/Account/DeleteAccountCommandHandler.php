<?php

namespace App\Application\Command\Handlers\Account;

use Exception;

use App\Api\Responder\ApiResponse;

use App\Domain\Shared\Account\AccountRole;
use App\Application\Command\Utils\AccountRepositoryFactory;
use App\Application\Usecases\account\AccountEraser;

use Symfony\Component\Validator\Validator\ValidatorInterface;



class DeleteAccountCommandHandler
{
    public function __construct(
        private AccountEraser $eraser,
        private AccountRepositoryFactory $accountRepositoryFactory,
        private ValidatorInterface $validator
    ){}

    public function handle(string $userId, AccountRole $role): ApiResponse
    {
        try{
            $repository = $this->accountRepositoryFactory->create($role->value);
            $this->eraser->execute(
                id: $userId,
                repository: $repository
            );
            return ApiResponse::notice("Everything went successfully");
        }
        catch(Exception $exception){
            throw $exception;
        }
    }
}