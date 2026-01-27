<?php

namespace App\Domain\services\FileStorage;

enum FileOwnerType: string
{
    case USER = 'user';
    case MESSAGE = 'message';
    case CANDIDATE = 'candidate';
}