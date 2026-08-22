<?php

namespace App\Application\DTO;

class ChangePassword
{
    public function __construct(
        public string $email,
        public string $password,
        public string $verificationToken,
    ){}
}