<?php

namespace App\Domain\Shared\Account;

enum AccountRole : string
{
    case USER = 'ROLE_USER';
    case AGENT = 'ROLE_AGENT';
    case CANDIDATE = 'ROLE_CANDIDATE';
}