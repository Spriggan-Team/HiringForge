<?php

namespace App\Domain\Shared\Account;

enum AccountRole : string
{
    case USER = 'ROLE_USER'; // represent a company
    case AGENT = 'ROLE_AGENT';   //represent a agent made by an user
    case CANDIDATE = 'ROLE_CANDIDATE'; //represent a candidate

    case SUPER_ADMIN = 'SUPER_ADMIN';     //Just an user with super authority avec others "user" account

    case UNKNOWN = "UNKNOWN"; //-- for handling error

    public static function fromString(string  $str):?self
    {
        return self::tryFrom(strtoupper($str));
    }
}