<?php

namespace App\Domain\Security;

use DateTimeImmutable;

interface TokenBlacklistRepositoryInterface
{
    /**
     *  A Adds a token to the blacklist until its natural expiration date.
     */
    public function blacklist(string $jti, DateTimeImmutable $expiresAt, string $reason): void;

    /**
     *  Checks whether a token is on the blacklist.
     */
    public function isBlacklisted(string $jti): bool;
}