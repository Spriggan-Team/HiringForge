<?php

namespace App\Api\DTO\JobOffer;


use Symfony\Component\Validator\Constraints as Assert;


class DeleteJobOfferRequest
{
    public function __construct(
        #[Assert\Uuid]
        #[Assert\NotNull]
        public string $uuid,

        #[Assert\Uuid]
        #[Assert\NotNull]
        public string $accountId,
    ){}
}

