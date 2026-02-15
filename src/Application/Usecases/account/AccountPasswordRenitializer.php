<?php

namespace App\Application\Usecases\account;

use App\Domain\Email\EmailPurpose;
use App\Domain\Email\EmailRepositoryInterface;
use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\User\UserRepositoryInterface;

use App\Domain\User\UserId;
use App\Domain\Shared\PlainPassword;

use App\Domain\Shared\PasswordHasherInterface;


class AccountPasswordRenitializer
{
    public function __construct(
        private EmailRepositoryInterface $emailRepository,
        private PasswordHasherInterface $hasher
    ){}

    /**
     * This function reset a user's password based
     */
    public function execute(
        string $uuid,
        string $password,
        string $verificationCode,
        AccountRepositoryInterface $repository,
    )
    {
        $userId =  UserId::create($uuid);
        $plainPassword = new PlainPassword($password);

        $identity = $repository->exists($userId->value());
        $emailMessage  =  $this->emailRepository->getEmailWithPurpose($identity->email, EmailPurpose::VERIFICATION_CODE);

        if(trim($verificationCode) === $emailMessage->verificationCode()){
            $repository->changePassword(
                $identity->email,
                $this->hasher->hash($plainPassword->value())
            );
            $this->emailRepository->deleteEmail($emailMessage->id);
        }
    }
}