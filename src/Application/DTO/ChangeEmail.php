<?php

namespace App\Application\DTO;

class ChangeEmail
{
    public function __construct(
        public string $oldEMail,
        public string $newEmail,
        public string $password
    ){}
}