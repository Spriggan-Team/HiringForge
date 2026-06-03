<?php

namespace App\Application\Query\User;

interface UserQueryRepositoryInterface
{
    /**
     * @param string                                         $uuid is the user's id
     * @throws RessourceNotFound|InvalidArgumentException    This is raised when an user is not identify in the bdd
     * @return UserProfileItem                                  This is a view of all basics info about the user. It represents it profile information
     */
    public function fetchUserView(string $uuid): UserProfileItem;
}