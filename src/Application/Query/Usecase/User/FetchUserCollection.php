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
        return array_map(
            fn ($user) => new UserResponseDTO(
                id: $user->id()->value(),
                name: $user->name(),
                email: $user->email(),
                siret: $user->siret(),
                address: [
                    "city"  => $user->address()->city,
                    "street" => $user->address()->street,
                    "postalCode" => $user->address()->postalCode,
                    "country"    => $user->address()->country
                ]
            ),
            $this->repository->getAll()
        );
    }

}