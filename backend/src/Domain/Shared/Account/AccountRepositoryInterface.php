<?php

namespace App\Domain\Shared\Account;

use App\Domain\Shared\EmailAddress;
use App\Domain\Sharedp\KnownIdentity;


/**
 * Pool for transversal repository action.
 * The particuluarity here is that only the account table is indexed for requests
 */
interface AccountRepositoryInterface
{
    public function findAll(?int $skip=null, ?int $limit = null): array;

    /**
     * Check if an user exists in bdd (using wether his uuid or email)
     * This function throw  an Exception that indicates wether or not an user exist
     * @param ?string                   $uuid
     * @param ?string                   $email
     * @throws RessourceNotFound        This exception should be sent when a ressource is not found in bdd
     * @return KnownUserIdentity        contains basics information about user
     */
    public function exists(?string $uuid = null, ?EmailAddress $email = null): KnownIdentity;

    /**
     * As its name indicate, this function is used to change the password of an existing user
     * @return void;
     */
    public function changePassword(string $email, string $hash): void;

    
    /**
     * This function hepl us changing the email in the bdd
     * @throws Exception
     */
    public function changeEmail(string $email): void;

    
    public function delete():void;
}