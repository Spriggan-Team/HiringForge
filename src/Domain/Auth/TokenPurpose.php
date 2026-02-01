<?php

namespace App\Domain\Auth;

enum TokenPurpose: string
{
    case REGISTER_UPLOAD = 'register_upload';
    case AUTHENTIFICATE = 'login';
}