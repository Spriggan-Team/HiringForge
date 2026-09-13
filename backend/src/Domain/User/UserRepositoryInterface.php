<?php

namespace App\Domain\User;

use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Shared\Address;
use App\Domain\Shared\KnownIdentity;


interface UserRepositoryInterface 
{

    /**
     * Retrieve information about recruiter.
     *
     * @return array{
     *     firstName: string,
     *     lastName: string,
     *     description: ?string,
     *     email: string,
     *     image: ?array{
     *         id: int,
     *         name: string,
     *         mime: string,      
     *     }
     * }
     */
    public function getRecruiterView(string $userId): array;

    /**
     * A method to save a new ressource in storage/bdd
     * @param  User $user represents the user to persist
     * @return void 
     * @throws \Exception|ResourceCreationRejected
     * use to create/update a new ressource in the bdd
    */
    public function save(User $user): void;


    /**
     * Check an user existance in bdd
     */
    public function exists(string $userId): bool;


    /**
     * This function verify if a specific user exist & is registered in the 
     * database.
     * @throws ResourceNotFoundException tell if an user has been found or not
     */
    public function assertExist(?string $uuid = null, ?string $email = null): KnownIdentity;



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
     * @throws ResourceNotFoundException    this exception should be throw when the ressouce does not exist in bdd
     * @return ?User                the retriving actor (user, candidate, agent ...)
     */
    public function findByEmail(string $email): User;

    
    /**
     * 
     * This function must only be use when your want to apply a consequent/very important rules
     * @param string                $uuid represents the uniq identifier of an actor stored in the bdd
     * @return ?User
     * @throws ResourceNotFoundException    this exception should be throw when the ressouce does not exist in bdd
     * return the specified actor requested if founded in the bdd storage
     */
    public function findById(string $uuid): User;


    /** 
     * retreive user company id 
     * @throws \Exception
    */
    public function getOrganizationId(string $userId): string ;

    

    /** Handle user deltion (recruiter) */
    public function delete(string $id): void;


    /**
     * @return array{
     *      lastName: string,
     *      firstName: string;
     * }
     */
    public function getLightModelById(string $userId): ?array;
}