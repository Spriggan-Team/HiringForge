<?php

namespace App\Domain\Email;

use App\Domain\Shared\Account\AccountFlowPurpose;

class EmailMessage
{
    private function __construct(
        public ?string $id = null,
        public ?string $title =null,
        public string $description,
        public AccountFlowPurpose $purpose,
        public ?string $code =null,
    ) {}

    public static function create(
        ?string $id = null,
        ?string $title =null,
        string $description,
        AccountFlowPurpose $purpose,
        ?string $code =null
    ): self
    {
        return new self(
            $id,
            $title,
            $description,
            $purpose,
            $code,
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