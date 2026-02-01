<?php

namespace App\Domain\User;

use App\Domain\Shared\Actor\ActorRepositoryInterface;

interface UserRepositoryInterface  extends ActorRepositoryInterface
{
    /**
     * @param string                $uuid represents the uniq identifier of an actor stored in the bdd
     * @return ?User
     * @throws RessourceNotFound    this exception should be throw when the ressouce does not exist in bdd
     * return the specified actor requested if founded in the bdd storage
     */
    public function findById(string $uuid): ?User;


    /**
     * This function is meant to retreive an actor from the bdd uisng his email
     * @return ?User                the retriving actor (user, candidate, agent ...)
     * @throws RessourceNotFound    this exception should be throw when the ressouce does not exist in bdd
     */
    public function findByEmail(string $email): ?User;


    /**
     * @param  User $user represents the user to persist
     * @return void 
     * @throws Exception
     * use to create/update a new ressource in the bdd
    */
    public function save(User $user): void;


    /**
     * @return void
     * @throws RessourceNotFound this exception should be throw when a user does not exist in bdd
     * Used for deleting User ressouce from bdd 
     */
    public function delete(string $uuid): void;

}