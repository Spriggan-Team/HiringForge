<?php

namespace App\Application\Query\Usecase\User;

use App\Api\DTO\User\UserResponseDTO;
use App\Api\DTO\User\GetUserCollectionRequest;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\UserRepository;

class FetchUserCollection
{
    
    public function __construct(private UserRepository $repository){}

    public function execute(GetUserCollectionRequest $query): array
    {
        $collection = $this->repository->getAll();

        for ($i=0; $i < count($collection) ; $i++) { 
            $collection = new UserResponseDTO(
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