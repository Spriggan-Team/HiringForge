<?php

namespace App\Application\Command\Handlers\User;

use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserRepository;

class UserEraser
{

    public function __construct(
        private UserRepository $userRepository,
        private AccountRepositoryInterface $accountRepository
    ){}

    /**
     * @throws Exception
     * @return void
     */
    public function execute(
        string $userId
    ): void
    {

    }
}