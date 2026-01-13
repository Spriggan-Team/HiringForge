<?php

namespace App\Application\Query\Usecase\User;

use App\Api\DTO\User\UserResponseDTO;
use App\Api\DTO\User\GetUserRequest;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\UserRepository;


class FetchUser {
    
    public function __construct(private UserRepository $repository){}

    public function execute(GetUserRequest $query): UserResponseDTO
    {
        $user = $this->repository->getById($query->uuid);
        
        $accountResponse = new UserResponseDTO(
            id: $user->getId(),
            name: $user->getName(),
            email: $user->getEmail(),
            siret: $user->getSiret(),
            password: $user->getPassword()
        );

        return $accountResponse;
    }
}

?>