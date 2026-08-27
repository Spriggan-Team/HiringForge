<?php

namespace App\Application\DTO\EmploymentOffer;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateOfferRequestDto
{
    public function __construct(
        #[Assert\NotBlank]
        public string $candidateId,

        #[Assert\NotBlank]
        public string $applicationId,

        #[Assert\Type(\DateTimeInterface::class)]
        #[Assert\GreaterThan('now')]
        public ?\DateTimeImmutable $expiredAt = null,

        #[Assert\Length(max: 255)]
        public ?string $title = null,

        public string $jobTitle,

        public ?float $salary = null,
        
        public ?string $message = null,
    ) {}
}