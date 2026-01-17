<?php

namespace App\Api\DTO\JobOffer;

use Symfony\Component\Validator\Constraints as Assert;


final class CreateJobOfferRequest
{

    public function __construct(
        #[Assert\NotBlank]
        public string $title,
        
        #[Assert\NotBlank]
        #[Assert\Uuid()]
        public string $userId,

        #[Assert\NotBlank]
        #[Assert\NotNull]
        public array $content,
    ){}
}