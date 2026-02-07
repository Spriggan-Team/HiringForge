<?php

namespace App\Domain\Email;

class EmailMessage
{
    public function __construct(
        public ?string $id = null,
        public ?string $title =null,
        public string $description,
        public ?VerificationCode $code =null
    ) {}

    public function verificationCode(): ?string
    {
        if($this->code){
            return $this->code->value();
        }
        return null;
    }
}