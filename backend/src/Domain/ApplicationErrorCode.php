<?php

namespace App\Domain;

enum ApplicationErrorCode: string{
    //-- otp
    case EXPIRED_OTP = "expired_otp";
    case INVALID_OTP = "invalid_otp";
    
    //-- account
    case ACCOUNT_ALREADY_EXISTS = "account_already_exists";
    case ACCOUNT_NOT_FOUND = "account_not_found";
}