<?php

namespace App\Application\DTO\JobOffer;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateJobOfferRequest
{

    public function __construct(
        #[Assert\NotBlank]
        public string $title,

        #[Assert\NotBlank]
        #[Assert\NotNull]
        public array $content,
        
        /**
         * @param array
         */
        public array $categories,

        
        /**
         * This one must represent one image
         * @var mixed
         */
        public mixed $image = null,
    ){}
}