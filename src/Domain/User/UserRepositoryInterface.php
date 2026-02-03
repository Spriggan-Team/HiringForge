<?php

namespace App\Domain\User;

use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\Actor\ActorRepositoryInterface;

interface UserRepositoryInterface  extends ActorRepositoryInterface
{
    /**
     * Check if an user exists in bdd (using wether his uuid or email)
     * @param ?string                   $uuid
     * @param ?string                   $email
     * @throws RessourceNotFound        This exception should be sent when a ressource is not found in bdd
     * @return KnownUserIdentity        contains basics information about user
     */
    public function exists(?string $uuid = null, ?EmailAddress $email = null): KnownUserIdentity;

    /**
     * @param string                $uuid is the user's id
     * @throws RessourceNotFound    This is raised when an user is not identify in the bdd
     * @return UserListItem         This is a view of all basics info about the user. It represents it profile information
     */
    public function fectchUserView(string $uuid): UserListItem;

    /**
     * 
     * This function must only be use when your want to apply a consequent/very important rules
     * @param string                $uuid represents the uniq identifier of an actor stored in the bdd
     * @return ?User
     * @throws RessourceNotFound    this exception should be throw when the ressouce does not exist in bdd
     * return the specified actor requested if founded in the bdd storage
     */
    public function findById(string $uuid): User;


    /**
     * 
     * This function must only be use when your want to apply a consequent/very important rules.
     * This function is meant to retreive an actor from the bdd uisng his email.
     * @throws RessourceNotFound    this exception should be throw when the ressouce does not exist in bdd
     * @return ?User                the retriving actor (user, candidate, agent ...)
     */
    public function findByEmail(string $email): User;


    /**
     * @param  User $user represents the user to persist
     * @return void 
     * @throws Exception
     * use to create/update a new ressource in the bdd
    */
    public function save(User $user): void;

    /**
     * As its name indicate, this function is used to change the password of an existing user
     * @return void;
     */
    public function changePassword(string $email, string $hash): void;


    /**
     * This function purpose is to delete an existing user stored in the bdd
     * @return void
     * @throws RessourceNotFound this exception should be throw when we try to delte an user that does not exist in bdd;
     */
    public function delete(UserId $uuid): void;
    
}