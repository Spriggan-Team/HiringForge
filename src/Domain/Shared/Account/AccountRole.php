<?php

namespace App\Domain\Shared\Account;

enum AccountRole : string
{
    case USER = 'USER';
    case AGENT = 'AGENT';
    case CANDIDATE = 'CANDIDATE';

    public static function fromString(string  $str):?self
    {
        return self::tryFrom(strtoupper($str));
    }
}