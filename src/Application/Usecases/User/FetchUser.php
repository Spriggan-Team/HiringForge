<?php

namespace App\Application\Usecases\User;

use App\Domain\User\UserId;
use App\Domain\User\UserListItem;
use App\Domain\User\UserRepositoryInterface;


class FetchUser {
    public function __construct(private UserRepositoryInterface $repository){}

    /**
     * This usecase is able to get an user from the bdd
     * with domain validation
     * @throws RessourceNotFound|DomainException
     */
    public function execute(string $id): UserListItem
    {
        $userId = new UserId($id);

        $this->repository->exists($userId->value());
        $userView = $this->repository->fectchUserView($userId->value());

        return $userView;
    }
}

?>