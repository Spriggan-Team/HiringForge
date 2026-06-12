<?php

namespace App\Domain;

enum ApplicationErrorCode: string{
    //-- global
    case RESSOURCE_CREATION_FAILED = "ressources_creation_failed";

    //-- otp
    case EXPIRED_OTP = "expired_otp";
    case INVALID_OTP = "invalid_otp";
    
    //-- account & account
    case ACCOUNT_ALREADY_EXISTS = "account_already_exists";
    case ACCOUNT_NOT_FOUND = "account_not_found";
    case INVALID_CREDENTIALS = "invalid_credential";
    case COMPANY_ALREADY_REGISTERED = "company_already_registered";

    //-- file
    case FILE_MISMATCH_TYPE = "file_mismatch_type";
    case FILE_SIZE_EXCEEDED = "file_size_exceeded";
    case FILE_TIME_EXCEEDED = "file_time_exceeded";
}