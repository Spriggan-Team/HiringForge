<?php

namespace App\Application\DTO\Offer;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateOfferRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        public string $candidateId,

        #[Assert\NotBlank]
        public string $applicationId,

        #[Assert\NotBlank]
        #[Assert\DateTime]
        public string $expiredAt,

        #[Assert\Length(max: 255)]
        public ?string $title = null,

        #[Assert\Positive]
        public ?float $salary = null,

        public ?string $message = null,
    ) {
    }
}