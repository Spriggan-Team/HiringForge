<?php

namespace App\Application\DTO\Agent;

class CreateAgentCommand{
    public function __construct(
        public string $email,
        public string $password,
        public string $verificationCode,
        public string $authorId,
    ){}
}