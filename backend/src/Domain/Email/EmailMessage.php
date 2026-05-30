<?php

namespace App\Domain\Email;

use App\Domain\Shared\Account\AccountFlowPurpose;

class EmailMessage
{
    private function __construct(
        public string $description,
        public AccountFlowPurpose $purpose,
        public ?string $id = null,
        public ?string $title =null,
        public ?string $code =null,
        public EmailCategory $type = EmailCategory::DEFAULT
    ) {}

    public static function create(
        AccountFlowPurpose $purpose,
        string $description,
        ?string $id = null,
        ?string $title = null,
        ?string $code =null,
        EmailCategory $type = EmailCategory::DEFAULT
    ): self
    {
        return new self(
            $description,
            $purpose,
            $id,
            $title,
            $code,
            $type
        );
    }

    public function verificationCode(): ?string
    {
        if($this->code){
            return $this->code;
        }
        return null;
    }
}