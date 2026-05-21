<?php

namespace App\Domain\Agent;

use App\Domain\Shared\Account\Account;

class Agent implements Account
{
    public function id(): string
    {
        throw new \Exception('Not implemented');
    }
    
    public function email(): string
    {
        throw new \Exception('Not implemented');
    }

    public function passwordHash(): string
    {
        throw new \Exception('Not implemented');
    }
}