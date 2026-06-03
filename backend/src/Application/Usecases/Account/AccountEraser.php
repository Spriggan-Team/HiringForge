<?php

namespace App\Application\Usecases\Account;

use App\Domain\Shared\Account\AccountRepositoryInterface;

class AccountEraser
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository
    )
    {}

    /**
     * @throws RessourceNotFound is thrown when no user if found
     */
    public function execute(
        string $id,
    ):void
    {}
}