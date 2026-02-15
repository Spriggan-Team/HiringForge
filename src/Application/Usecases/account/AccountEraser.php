<?php

namespace App\Application\Usecases\account;

use App\Domain\Shared\Account\AccountRepositoryInterface;

class AccountEraser
{
    public function __construct()
    {}

    /**
     * @throws RessourceNotFound is thrown when no user if found
     */
    public function execute(
        string $id,
        AccountRepositoryInterface $repository
    ):void
    {
        $repository->delete($id);
    }
}