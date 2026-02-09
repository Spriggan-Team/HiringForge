<?php

namespace App\Application\DTO\JobOffer;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateJobOffer
{

    public function __construct(
        #[Assert\NotBlank]
        public string $title,


        #[Assert\NotBlank]
        #[Assert\NotNull]
        public array $content,

        /**
         * This one must represent one image
         * @var mixed
         */
        public mixed $image = null,
        /**
         * @param array<int>
         */
        public array $categories,
    ){}
}