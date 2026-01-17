<?php


namespace App\Application\Command\Usecase\Account;


use App\Api\DTO\User\OverwriteUserRequest;
use App\Domain\User\UserRepositoryInterface;


class UserOverwritter
{
    public function __construct(private UserRepositoryInterface $repository){}

    public function execute(OverwriteUserRequest $command):void
    {
        $user = $this->repository->getById($command->uuid);
        $this->repository->save($user);
    }
}