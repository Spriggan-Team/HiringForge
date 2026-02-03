<?php

namespace App\Domain\Email;

enum EmailPurpose: string
{
    case VERIFICATION_CODE = "verification_code";
}