<?php

namespace App\Application\DTO;


class ChangeEmail
{
    public function __construct(
        public string $oldEmail,
        public string $newEmail,
        public string $password,
        public string $verificationToken
    ){}
}