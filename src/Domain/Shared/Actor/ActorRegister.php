<?php

namespace App\Domain\Shared\Actor;

/**
 * A DTO(Data transfer object) that represents what is provided after an user registering
 */
class ActorRegister
{
    public function __construct(
        public string $id,
        public array $failedImages = [],
    ){}
}