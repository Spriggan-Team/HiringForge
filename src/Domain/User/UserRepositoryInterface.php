<?php

namespace App\Domain\User;

use App\Domain\Shared\Account\AccountRepositoryInterface;

interface UserRepositoryInterface 
{
    
    /**
     * @param string                                         $uuid is the user's id
     * @throws RessourceNotFound|InvalidArgumentException    This is raised when an user is not identify in the bdd
     * @return UserListItem                                  This is a view of all basics info about the user. It represents it profile information
     */
    public function fectchUserView(string $uuid): UserListItem;



    /**
     * A method to save a new ressource in storage/bdd
     * @param  User $user represents the user to persist
     * @return void 
     * @throws Exception
     * use to create/update a new ressource in the bdd
    */
    public function save(User $user): void;



    /**
     *      This function take in a user and change all of its property except those sensitive
     *      (such as email and password...)
     * @param User          The user aggregate you want to change
     * @param string[]      $deleteImages the array of name of the files you want to erase
     * @return void
     */
    public function change(User $user, string $uuid, ?array $deleteImages = null): void;


    /**
     * 
     * This function must only be use when your want to apply a consequent/very important rules.
     * This function is meant to retreive an actor from the bdd uisng his email.
     * @throws RessourceNotFound    this exception should be throw when the ressouce does not exist in bdd
     * @return ?User                the retriving actor (user, candidate, agent ...)
     */
    public function findByEmail(string $email): User;

    /**
     * 
     * This function must only be use when your want to apply a consequent/very important rules
     * @param string                $uuid represents the uniq identifier of an actor stored in the bdd
     * @return ?User
     * @throws RessourceNotFound    this exception should be throw when the ressouce does not exist in bdd
     * return the specified actor requested if founded in the bdd storage
     */
    public function findById(string $uuid): User;
    
}