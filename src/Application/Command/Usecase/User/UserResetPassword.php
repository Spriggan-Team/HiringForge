<?php

namespace App\Application\Command\Usecase\User;

use App\Domain\Email\EmailPurpose;
use App\Domain\Email\EmailRepositoryInterface;
use App\Domain\User\UserRepositoryInterface;

use App\Domain\User\UserId;
use App\Domain\Shared\PlainPassword;

use App\Domain\Shared\PasswordHasherInterface;


class UserResetPassword
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EmailRepositoryInterface $emailRepository,
        private PasswordHasherInterface $hasher
    ){}

    /**
     * This function reset a user's password based
     */
    public function execute(string $uuid, string $password, string $verificationCode)
    {
        $userId = new UserId($uuid);
        $plainPassword = new PlainPassword($password);

        $identity = $this->userRepository->exists($userId->value());
        $emailMessage   =  $this->emailRepository->getEmailWithPurpose($identity->email, EmailPurpose::VERIFICATION_CODE);

        if(trim($verificationCode) === $emailMessage->verificationCode()){
            $this->userRepository->changePassword(
                $identity->email,
                $this->hasher->hash($plainPassword->value())
            );
            $this->emailRepository->deleteEmail($emailMessage->id);
        }
    }
}