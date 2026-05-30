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
    public function getLastVerificationTokenWithPurpose(string $email, AccountFlowPurpose $purpose): ?OTP;

    /**
     * This function send an otp key to an user
     * @param OTP      $the opt verification
     * @throws Exception
     * @return void 
     */
    public function save(string $email, OTP $otp): void;

}