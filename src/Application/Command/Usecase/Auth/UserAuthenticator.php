<?php

namespace App\Application\Command\Usecase\Auth;

use App\Application\DTO\AuthentificateActor;
use App\Domain\Shared\PasswordHasherInterface;
use App\Domain\User\UserRepositoryInterface;

class UserAuthenticator
{
    public function __construct(
        private UserRepositoryInterface $repository,
        private PasswordHasherInterface $hasher
    ){}

    /**
     * @return ?string the userid
     */
    public function execute(AuthentificateActor $actor): ?string
    {
        $user =  $this->repository->findByEmail($actor->email);
        if($user && $this->hasher->verify($actor->password, $user->passwordHash())){
            return $user->id();
        }
        return null;
    }
}
