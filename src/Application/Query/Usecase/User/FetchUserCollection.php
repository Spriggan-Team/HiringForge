<?php

namespace App\Application\Query\Usecase\User;

use App\Api\DTO\User\UserResponseDTO;
use App\Api\DTO\User\GetUserCollectionRequest;
use App\Domain\User\UserRepositoryInterface;

class FetchUserCollection
{
    
    public function __construct(private UserRepositoryInterface $repository){}

    public function execute(GetUserCollectionRequest $query): array
    {
        $collection = $this->repository->getAll();

        for ($i=0; $i < count($collection) ; $i++) { 
            $collection = new UserResponseDTO(
                id: $collection[$i]->id()->value(),
                name: $collection[$i]-> name(),
                email: $collection[$i]->email(),
                siret: $collection[$i]->siret(),
                password: $collection[$i]->password()
            );
        }
        
        return $collection;
    }

}