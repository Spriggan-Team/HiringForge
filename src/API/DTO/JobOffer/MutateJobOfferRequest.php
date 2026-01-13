<?php

namespace App\Api\DTO\JobOffer;


use Symfony\Component\Validator\Constraints as Assert;

class MutateJobOfferRequest
{
    public function __construct(
        #[Assert\Uuid]
        public string $accountId,

        #[Assert\Uuid]
        public string $uuid,

        public ?string $title = null,
        public ?array $content = null,
    ){}
}