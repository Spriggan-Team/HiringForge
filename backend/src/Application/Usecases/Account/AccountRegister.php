<?php

namespace App\Application\Usecases\Account;

class AccountRegister
{
    public function __construct(
        public string $userId,

        /** @var string[] */
        public array $filesFailedGeneric = [],

        /** @var string[] */
        public array $filesFailedTimeout = [],

        /** @var string[] */
        public array $filesFailedSize = []
    )
    {}
}