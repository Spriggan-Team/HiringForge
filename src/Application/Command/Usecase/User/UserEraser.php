<?php

namespace App\Application\Command\Usecase\User;

use App\Api\DTO\User\DeleteUserRequest;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\UserRepository;


class UserEraser
{
    public function __construct(private UserRepository $repository){}

    public function execute(DeleteUserRequest $command)
    {
        $this->repository->delete($command->uuid);
    }
}