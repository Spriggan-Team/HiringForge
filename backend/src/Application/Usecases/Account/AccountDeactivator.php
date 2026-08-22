<?php

namespace App\Application\Usecases\Account;

use App\Domain\User\UserId;
use App\Domain\User\UserRepositoryInterface;


class AccountDeactivator
{
    public function __construct(private UserRepositoryInterface $repository){}

    public function execute(string  $accountId)
    {
        $this->repository->delete($accountId);
    }
}