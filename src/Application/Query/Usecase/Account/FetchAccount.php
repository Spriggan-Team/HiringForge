<?php

namespace App\Application\Query\Usecase\Account;

use App\Api\DTO\Account\AccountResponse;
use App\Api\DTO\Account\GetAccountRequest;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\AccountRepository;

class FetchAccount {
    
    public function __construct(private AccountRepository $repository){}

    public function execute(GetAccountRequest $query): AccountResponse
    {
        $account = $this->repository->getById($query->uuid);
        
        $accountResponse = new AccountResponse(
            id: $account->getId(),
            name: $account->getName(),
            email: $account->getEmail(),
            siret: $account->getSiret(),
            password: $account->getPassword()
        );

        return $accountResponse;
    }
}

?>