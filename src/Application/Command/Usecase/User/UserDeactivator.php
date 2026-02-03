<?php

namespace App\Application\Command\Usecase\User;

use App\Domain\User\UserId;
use App\Domain\User\UserRepositoryInterface;


class UserEraser
{
    public function __construct(private UserRepositoryInterface $repository){}

    public function execute(string  $command)
    {
        $this->repository->delete(new UserId($command));
    }
}