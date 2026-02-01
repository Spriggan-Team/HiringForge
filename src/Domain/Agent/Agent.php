<?php

namespace App\Domain\Agent;
use App\Domain\Shared\Actor\Actor;

class Agent implements Actor
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