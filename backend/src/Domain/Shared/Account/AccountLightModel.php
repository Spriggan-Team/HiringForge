<?php


namespace App\Domain\Shared\Account;

readonly class AccountLightModel
{
    public function __construct(
        public string $id,
        public string $firstName,
        public string $lastName,
        public string $email,
        public ?string $imageUrl = null,
    ) {}
}