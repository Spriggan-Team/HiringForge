<?php

namespace App\Application\DTO;

use App\Domain\Email\VerificationCode;

class ChangeEmail
{
    public function __construct(
        public string $oldEMail,
        public string $newEmail,
        public string $password,
        public string $verificationCode
    ){}
}