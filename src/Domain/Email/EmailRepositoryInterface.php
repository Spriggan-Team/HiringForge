<?php

namespace App\Domain\Email;

use App\Domain\Exception\RessourceNotFound;

interface EmailRepositoryInterface
{

    /**
     * This function is able to return a specific type of email stored in the bdd
     * @param string                $email the email associated
     * @throws RessourceNotFound    Indicate that a ressource has not been found or detected
     * @param  EmailPurpose 
     * @return EmailMessage
     */
    public function getEmailWithPurpose(string $email, EmailPurpose $purpose): EmailMessage;

    /**
     * This function keep trace of an message sent to the user in the bdd 
     * @param EmailMessage      $the message we want to send to the user
     * @return void 
     */
    public function save(EmailMessage $message): void;

    /**
     * This function allow you to delete an email registered in the bdd
     * @throws RessourceNotFound    Indicate that a ressource has not been found or detected
     *                              Here this exception can only be raised if an id is actually provided
     *                              and is not valid or does not exist in the bdd
     */
    public function deleteEmail(?string $id = null):void;
}