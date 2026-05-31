<?php

namespace App\Application\DTO\Auth;

/**
 * A DTO(Data transfer object) that represents what is provided after an user registering
 */
class AcountRegisterResult
{
    public function __construct(
        public string $id,
        public array $failedImages = [],
    ){}
}