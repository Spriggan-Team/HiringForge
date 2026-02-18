<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Global\DiscriminationMap\Account;

use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\Shared\EmailAddress;
use App\Domain\Sharedp\KnownIdentity;

class AccountRepository implements AccountRepositoryInterface
{

    public function findAll(?int $skip = null, ?int $limit = null): array
    {
        throw new \Exception('Not implemented');
    }

    public function exists(?string $uuid = null, ?EmailAddress $email = null): KnownIdentity
    {
        throw new \Exception('Not implemented');
    }

    public function changeEmail(string $email): void
    {
        throw new \Exception('Not implemented');
    }

    public function changePassword(string $email, string $hash): void
    {
        throw new \Exception('Not implemented');
    }

}