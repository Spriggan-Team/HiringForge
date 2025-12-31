<?php

namespace App\Application\Query\Usecase\Account;

use App\Api\DTO\Account\AccountResponse;
use App\Api\DTO\Account\GetAccountCollectionRequest;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\AccountRepository;

class FetchAccountCollection{
    
    public function __construct(private AccountRepository $repository){}

    public function execute(GetAccountCollectionRequest $query): array
    {
        $collection = $this->repository->getAll();

        for ($i=0; $i < count($collection) ; $i++) { 
            $collection = new AccountResponse(
                id: $collection[$i]->getId(),
                name: $collection[$i]->getName(),
                email: $collection[$i]->getEmail(),
                siret: $collection[$i]->getSiret(),
                password: $collection[$i]->getPassword()
            );
        }
        
        return $collection;
    }

}