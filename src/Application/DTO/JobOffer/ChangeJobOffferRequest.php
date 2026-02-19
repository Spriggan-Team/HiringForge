<?php

namespace App\Application\DTO\JobOffer;



class ChangeJobOffferRequest
{
    public function __construct(
        public string $uuid,

        public ?string $title = null,
        public ?array $content = null,

        public mixed $image,
    ){}
}