<?php

namespace App\Domain\Shared\Actor;

enum ActorRole : string
{
    case USER = 'user';
    case AGENT = 'agent';
    case CANDIDATE = 'candidate';
}