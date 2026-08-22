<?php

namespace App\Domain\File;

enum MediaOwnerType: string
{
    case USER = 'user';
    case MESSAGE = 'message';
    case CANDIDATE = 'candidate';
    case COMPANY = 'company';
}