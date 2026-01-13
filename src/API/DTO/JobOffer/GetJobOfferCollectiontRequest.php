<?php

namespace App\Api\DTO\JobOffer;

use Symfony\Component\Validator\Constraints as Assert;

class GetJobOfferCollectiontRequest
{
    public function __construct(
        #[Assert\Uuid]
        public string $accountId
    ){}
}