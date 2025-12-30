<?php

namespace App\Application\Query\Usecase\Account;

use App\Api\DTO\Account\GetAccountCollectionRequest;
use App\Infrastructure\Persistence\MySQL\Repositories\AccountRepository;

class FetchAccountCollection{
    
    public function __construct(private AccountRepository $repository){}

    public function execute(GetAccountCollectionRequest $query): array
    {
        $collection = $this->repository->getAll();
        return $collection;
    }

}