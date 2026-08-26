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

        #[Assert\NotNull]
        #[Assert\Type(\DateTimeInterface::class)]
        #[Assert\GreaterThan('now')]
        public string $expiredAt,

        #[Assert\Length(max: 255)]
        public ?string $title = null,

        public string $jobTitle,

        #[Assert\Positive]
        public ?float $salary = null,
        
        public ?string $message = null,
    ) {}
}