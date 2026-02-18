<?php

namespace App\Application\Usecases\User;

use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\User\UserId;
use App\Domain\User\UserListItem;
use App\Domain\User\UserRepositoryInterface;


class FetchUser {
    public function __construct(
        private UserRepositoryInterface $userRepositoryRepository,
        private AccountRepositoryInterface $accountRepository
    ){}

    /**
     * This usecase is able to get an user from the bdd
     * with domain validation
     * @throws RessourceNotFound|DomainException
     */
    public function execute(string $id): UserListItem
    {
        $userId = new UserId($id);

        $this->accountRepository->exists($userId->value());
        $userView = $this->userRepositoryRepository->fectchUserView($userId->value());

        return $userView;
    }
}

?>