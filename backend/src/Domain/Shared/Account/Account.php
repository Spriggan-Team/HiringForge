<?php

namespace App\Domain\Shared\Account;

interface Account
{
    public function id(): string;
    public function email(): string;
    public function passwordHash(): string;
}