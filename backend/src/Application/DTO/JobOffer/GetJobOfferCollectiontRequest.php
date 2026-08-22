<?php

namespace App\Application\DTO\JobOffer;

use Symfony\Component\Validator\Constraints as Assert;

class GetJobOfferCollectiontRequest
{
    public function __construct(
        public ?int $skip = null,
        public ?int $limit = null 
    ){}
}