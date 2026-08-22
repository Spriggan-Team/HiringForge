<?php

namespace App\Application\DTO\JobOffer;

final class RequiredLanguageRequest
{
    public function __construct(
        public int $languageId,
        public string $level
    ){}
}