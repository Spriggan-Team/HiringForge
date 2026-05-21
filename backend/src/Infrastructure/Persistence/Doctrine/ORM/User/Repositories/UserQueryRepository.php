<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\User\Repositories;

use App\Application\Query\Handlers\User\UserProfileItem;
use App\Application\Query\Handlers\User\UserQueryRepositoryInterface;


/**
 * Only used for display user data under special view...
 */
class UserQueryRepository implements UserQueryRepositoryInterface
{
    public function __construct(){}

    public function fetchUserView(string $uuid): UserProfileItem
    {
        throw new \Exception('Not implemented');
    }
}