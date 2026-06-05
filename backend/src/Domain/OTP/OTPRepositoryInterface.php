<?php

namespace App\Domain\OTP;

use App\Domain\Exception\RessourceNotFound;
use App\Domain\Shared\Account\AccountFlowPurpose;
use App\Domain\Shared\Account\AccountRole;

interface OTPRepositoryInterface
{

    /**
     * This function is able to return a specific type of verification token stored in the bdd.
     * Precisely it returns the last one of them registered
     * @param  string                $email the email associated
     * @throws RessourceNotFound     Indicate that a ressource has not been found or detected
     * @param  AccountFlowPurpose 
     * @return OTP
     */
    public function getLastVerificationTokenWithPurpose(string $email, AccountFlowPurpose $purpose): OTP;

    /**
     * This function send an otp key to an user
     * @param OTP      $the opt verification
     * @throws Exception
     * @return void 
     */
    public function save(string $email, OTP $otp): void;
    
    
    /**
     * Updates the mutable properties of an existing OTP token (primarily the attempts counter).
     *
     * This method locates the active OTP token associated with the given email and purpose.
     * It synchronizes the domain state changes with the infrastructure storage. Crucially, 
     * optional or nullable domain properties (like email or account links) will NOT overwrite 
     * existing database values if they are null in the provided Domain Object, preventing 
     * accidental data loss.
     *
     * @param string $email The email address used to locate the associated account.
     * @param OTP $otp The Domain Object containing the updated state (e.g., incremented attempts).
     * * @throws RessourceNotFound If no matching account or existing OTP token is found in the database.
     * @return void
     */
    public function update(string $email, OTP $otp): void;
}