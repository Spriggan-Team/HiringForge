<?php


namespace App\Application\Command\Usecase\Account;

use App\Domain\Shared\EmailAddress;
use App\Domain\Email\EmailMessage;
use App\Domain\Email\VerificationCode;

use App\Domain\Email\EmailRepositoryInterface;
use App\Domain\Email\EmailServicesInterface;
use App\Domain\User\UserRepositoryInterface;


class VerificationCodeSender
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EmailRepositoryInterface $emailRepository,
        private EmailServicesInterface  $emailServices,
    ){}

    public function execute(string $email):void
    {
        $identity = $this->userRepository->exists(uuid: null, email: new EmailAddress($email));

        $emailMessage = new EmailMessage(
            description: "This is a verification for your to confirm your identity",
            code: new VerificationCode()        //Generate a verification code
        );

        $this->emailRepository->save($emailMessage);

        $this->emailServices->sendTo(
            receiver: $identity->email,
            document: $this->emailServices->buildEmail($emailMessage)
        );
    }
}