<?php

namespace App\Application\Command\Usecase\User;


use App\Domain\Shared\EmailAddress;
use App\Domain\Shared\PasswordHasherInterface;
use App\Domain\Shared\PlainPassword;
use App\Domain\User\UserRepositoryInterface;



class UserResetEmail
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordHasherInterface $hasher
    ){}

    /**
     * This function enforce special security for 
     * changin email.
     * @throws RessourceNotFound
     */
    public function execute(
        string $oldEmail,
        string $newEmail,
        string $password
    )
    {
        $oldValue = new EmailAddress($oldEmail);
        $newValue = new EmailAddress($newEmail);
        $plainPassword = new PlainPassword($password);

        $user = $this->userRepository->exists(null, $oldValue);
        
        if($user && $this->hasher->verify($plainPassword->value(), $user->password))
        {
            $this->userRepository->changeEmail($newEmail);
            return;
        }
    }

}