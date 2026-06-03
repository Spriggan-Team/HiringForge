<?php

namespace App\Application\Usecases\User;

use App\Domain\User\UserId;
use App\Application\Query\User\UserListItem;
use App\Application\Query\User\UserProfileItem;
use App\Application\Query\User\UserQueryRepositoryInterface;


class FetchUser {
    public function __construct(
        private UserQueryRepositoryInterface $userQueryRepository,
    ){}

    /**
     * Application use case responsible for retrieving
     * a lightweight user projection.
     *
     * This use case operates on the query side and
     * returns a read model optimized for presentation.
     * It does not reconstruct the full domain aggregate.
     *
     * @throws RessourceNotFound
     */
    public function execute(string $id): UserProfileItem
    {
        $userId = new UserId($id);

        return $this->userQueryRepository
            ->fetchUserView($userId->value());
    }
}

?>