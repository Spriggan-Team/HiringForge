<?php

namespace App\Domain\Agent;

use App\Domain\Shared\Account\Account;
use App\Domain\Shared\EmailAddress;

class Agent implements Account
{
    public function __construct(
        private EmailAddress $email,
        private string $authorId,
        private string $passwordHash,
        private ?string $id = null,
    ){}


    public function id(): string
    {
        return $this->id;
    }
    
    public function email(): string
    {
        return $this->email->value();
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function authorId() : string {
        return $this->authorId;
    }
}