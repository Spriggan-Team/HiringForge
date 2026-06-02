<?php

namespace App\Api\Responder;

enum ApiResponseCode: string {
    //-- otp
    case EXPIRED_OTP = "expired_otp";

    //-- account
    case ACCOUNT_ALREADY_EXISTS = "account_already_exists";
    case ACCOUNT_NOT_FOUND = "account_not_found";
}