<?php

namespace App\Application\DTO;

class ChangePassword
{
    public function __construct(
        public string $uuid,
        public string $password,
        public string $verificationCode,
    ){}
}