<?php

namespace App\Application\DTO\JobOffer;


use Symfony\Component\Validator\Constraints as Assert;

class ChangeJobOffferRequest
{
    public function __construct(
        #[Assert\Uuid]
        public string $uuid,

        public ?string $title = null,
        public ?array $content = null,

        public mixed $image,
    ){}
}