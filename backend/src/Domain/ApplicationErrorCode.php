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

    //-- employment offer
    case ACTIVE_EMPLOYMENT_OFFER_EXISTS = "active_employment_offer_exists";
    case EMPLOYMENT_OFFER_NOT_FOUND = "employment_offer_not_found";
    case INVALID_EMPLOYMENT_OFFER_STATE = "invalid_employment_offer_state";

    //-- auth/login
    case AUTH_ACCESS_EXPIRED = "access_expired";

    //-- file
    case FILE_MISMATCH_TYPE = "file_mismatch_type";
    case FILE_SIZE_EXCEEDED = "file_size_exceeded";
    case FILE_TIME_EXCEEDED = "file_time_exceeded";

    //-- resume
    case UNALLOW_RESUME_DELETION = "unallow_deletion_resume";
}