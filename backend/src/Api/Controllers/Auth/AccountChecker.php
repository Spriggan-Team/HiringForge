<?php

namespace App\Api\Controllers\Auth;

use App\Application\DTO\Auth\AuthenticatedPerson;
use App\Domain\Shared\Account\AccountRepositoryInterface;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;



class AccountChecker implements UserCheckerInterface
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository
    ) {}

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof AuthenticatedPerson) {
            return;
        }

        try {
            $this->accountRepository->exists($user->getId());
        } catch (\App\Domain\Exception\RessourceNotFound $exception) {
            throw new CustomUserMessageAccountStatusException('Account not found');
        } catch (\Exception $exception) {
            throw new CustomUserMessageAccountStatusException(
                'Something wrong happened while checking for authenticated user'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
