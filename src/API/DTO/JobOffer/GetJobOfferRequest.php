<?php

namespace App\Api\DTO\JobOffer; 


use Symfony\Component\Validator\Constraints as Assert;


class GetJobOfferRequest{
    public function __construct(
        #[Assert\Uuid]
        public string $accountId,
        #[Assert\Uuid]
        public string $uuid,
    ){}
}


?>