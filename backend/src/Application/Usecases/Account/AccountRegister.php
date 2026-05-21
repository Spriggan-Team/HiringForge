<?php

namespace App\Application\Usecases\Account;

class AccountRegister
{
    public function __construct(
        public string $id,
        public array $failedUploading = []
    )
    {}
}