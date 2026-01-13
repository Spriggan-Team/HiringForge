<?php

namespace App\Application\Command\Usecase\User;

use App\Api\DTO\User\MutateUserRequest;
use App\Domain\ValueObject\MergeRule;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\UserRepository;

/**
 * Deal with patch request directed toward account 
 */

class UserModifier
{
    public function __construct(private UserRepository $repository ){}

    public function execute(MutateUserRequest $command): void
    {
        $user = $this->repository->getById($command->uuid);
        
        if($command->email){
            $user->setEmail($command->email);
        }

        if($command->password){
            $user->setPassword($command->password);
        }

        $this->repository->save($user, MergeRule::PARTIAL_MERGE );
    }
}