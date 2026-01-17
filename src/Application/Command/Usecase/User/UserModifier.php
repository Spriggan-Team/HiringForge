<?php

namespace App\Application\Command\Usecase\User;

use App\Api\DTO\User\MutateUserRequest;
use App\Domain\User\UserRepositoryInterface;

/**
 * Deal with patch request directed toward account 
 */

class UserModifier
{
    public function __construct(private UserRepositoryInterface $repository ){}

    public function execute(MutateUserRequest $command): void
    {
        $user = $this->repository->getById($command->uuid);
        
        if($command->email){
            $user->setEmail($command->email);
        }

        if($command->password){
            $user->setPassword($command->password);
        }

        $this->repository->save($user);
    }
}