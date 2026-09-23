<?php

namespace App\Application\Usecases\User;

use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\User\UserRepositoryInterface;
use Smalot\PdfParser\Exception\NotImplementedException;

class UserEraser
{

    public function __construct(
        private UserRepositoryInterface $userRepository,
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
        throw new  NotImplementedException("Usecase : UserEraser is not implemented yet");
    }
}