<?php

namespace App\Application\Command\Usecase\Account;

use App\Api\DTO\Account\DeleteAccountRequest;
use App\Infrastructure\Persistence\MySQL\Repositories\AccountRepository;

class AccountEraser
{
    public function __construct(private AccountRepository $repository){}

    public function execute(DeleteAccountRequest $command)
    {
        $this->repository->delete($command->uuid);
    }
}