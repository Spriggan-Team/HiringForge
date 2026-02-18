<?php

namespace App\Domain\Shared\Account;


/**
 * AccountFlowPurpose
 *
 * Enum representing the context of a secure account action.
 * Defines why an OTP or verification is issued (e.g., password reset,
 * email change, or account verification).
 * Allows applying flow-specific TTLs(Times to live), validations, and restrictions.
 * Ensures tokens/codes are scoped to a specific action.
 */
enum AccountFlowPurpose: string
{
    case PASSWORD_RESET = "PASSWORD_RESET";
    case EMAIL_CHANGE = "EMAIL_CHANGE";         // Defines change context on email
    case SIGN_UP = "SIGNUP";
    case LOGIN_WARNING = "LOGIN_WARNING";

    public static function fromString(string $str): ?self
    {
        return self::tryFrom(strtoupper($str));
    }
}