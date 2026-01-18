<?php

namespace App\Application\Query\Usecase\User;

use App\Api\DTO\User\UserResponseDTO;
use App\Api\DTO\User\GetUserRequest;
use App\Domain\User\UserRepositoryInterface;


class FetchUser {
    
    public function __construct(private UserRepositoryInterface $repository){}

    public function execute(GetUserRequest $query): UserResponseDTO
    {
        $user = $this->repository->getById($query->uuid);
        
        $accountResponse = new UserResponseDTO(
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
        );

        return $accountResponse;
    }
}

?>