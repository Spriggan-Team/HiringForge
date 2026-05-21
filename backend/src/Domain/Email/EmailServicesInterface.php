<?php

namespace App\Domain\Email;

interface EmailServicesInterface
{

    /**
     * This function is use to inject the email message object data into the html template
     * to be send as an email to an user (actor)
     * @param 
     */
    public function prepareEmail(EmailMessage $emailMessage): \DOMDocument;

    /**
     * This function send an email to an existing user.
     * @param string                    $receiver This is email of the receiver
     * @throws EmailTransferException   This exception is raised when an error occur during the sending
     */
    public function sendTo(string $receiver, \DOMDocument $document): void;
}