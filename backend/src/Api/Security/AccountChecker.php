<?php

namespace App\Api\Security;

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
            $this->accountRepository->assertExist($user->getId());
        }catch (\App\Domain\Exception\ResourceNotFoundException $exception) {
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
