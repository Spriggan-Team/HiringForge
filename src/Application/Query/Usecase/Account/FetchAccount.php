<?php

namespace App\Application\Query\Usecase\Account;

use App\Api\DTO\Account\GetAccountRequest;
use App\Domain\Entity\Account;
use App\Infrastructure\Persistence\MySQL\Repositories\AccountRepository;

class FetchAccount {
    
    public function __construct(private AccountRepository $repository){}

    public function execute(GetAccountRequest $query): ?Account
    {
        $account = $this->repository->getById($query->uuid);
        return $account;
    }
}

?>