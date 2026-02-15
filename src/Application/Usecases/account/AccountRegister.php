<?php

namespace App\Application\Usecases\account;

class AccountRegister
{
    public function __construct(
        public string $id,
        public array $failedUploading = []
    )
    {}
}