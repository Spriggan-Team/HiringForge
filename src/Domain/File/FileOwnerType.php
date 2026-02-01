<?php

namespace App\Domain\File;

enum FileOwnerType: string
{
    case USER = 'user';
    case MESSAGE = 'message';
    case CANDIDATE = 'candidate';
}