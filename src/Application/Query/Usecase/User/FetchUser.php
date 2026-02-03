<?php

namespace App\Application\Query\Usecase\User;

use App\Domain\User\UserId;
use App\Domain\User\UserListItem;
use App\Domain\User\UserRepositoryInterface;


class FetchUser {
    public function __construct(private UserRepositoryInterface $repository){}

    public function execute(string $id): UserListItem
    {
        $userId = new UserId($id);
        $this->repository->exists($userId->value());
        $userView = $this->repository->fectchUserView($userId->value());
        return $userView;
    }
}

?>