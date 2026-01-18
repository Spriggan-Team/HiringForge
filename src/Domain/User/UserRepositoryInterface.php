<?php

namespace App\Domain\User;

use App\Domain\ValueObject\MergeRule;



interface UserRepositoryInterface {
    /**
     * @return void
     * @throws Exception
     * use to create/update a new ressource in the bdd
    */
    public function save(User $user): void;

    /**
     * @return User
     * return the specified user requested if founded in the bdd storage
     */
    public function getById(string $uuid): User;


    /**
     * @return User[]
     * @throws ApiRessourceNotFound
     * return an array of all the users existing in the bdd
     */
    public function getAll(): array;


    /**
     * @return void
     * @throws ApiRessourceNotFound
     * Used for deleting User ressouce from bdd 
     */
    public function delete(string $uuid): void;

}