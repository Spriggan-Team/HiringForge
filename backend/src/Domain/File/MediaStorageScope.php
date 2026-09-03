<?php

namespace App\Domain\File;


enum MediaStorageScope: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case TEST = 'tests';
}