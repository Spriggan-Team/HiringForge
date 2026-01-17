<?php

namespace App\Application\Command\Usecase\User;

use App\Api\DTO\User\DeleteUserRequest;
use App\Domain\User\UserRepositoryInterface;


class UserEraser
{
    public function __construct(private UserRepositoryInterface $repository){}

    public function execute(DeleteUserRequest $command)
    {
        $this->repository->delete($command->uuid);
    }
}