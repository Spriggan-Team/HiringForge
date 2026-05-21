<?php

namespace App\Domain\Shared\Account;

enum AccountRole : string
{
    case USER = 'USER'; // represent a company
    case AGENT = 'AGENT';   //represent a agent made by an user
    case CANDIDATE = 'CANDIDATE'; //represent a candidate
    case SUPER_ADMIN = 'SUPER';     //Just an user with super authority avec others "user" account

    public static function fromString(string  $str):?self
    {
        return self::tryFrom(strtoupper($str));
    }
}