<?php

namespace App\Domain\Services\FileStorage;

enum FileOwnerType: string
{
    case USER = 'user';
    case MESSAGE = 'message';
    case CANDIDATE = 'candidate';
}