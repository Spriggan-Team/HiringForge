<?php

namespace App\Domain\Email;

use App\Domain\Exception\RessourceNotFound;

interface EmailRepositoryInterface
{

    /**
     * This function is able to return an email store in the bdd
     * @param string                $email the email associated
     * @throws RessourceNotFound    Tell if a ressource has been found or not
     * @param  EmailPurpose 
     * @return EmailMessage
     */
    public function getEmailWithPurpose(string $email, EmailPurpose $purpose): EmailMessage;

    /**
     * This function keep trace of an message sent to the user in the bdd 
     * @param EmailMessage $the message we want to send to the user
     * @return void 
     */
    public function save(EmailMessage $message): void;
}