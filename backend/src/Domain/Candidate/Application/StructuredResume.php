<?php

namespace App\Domain\Candidate\Application;

final readonly class StructuredResume
{
    public function __construct(
        public ?string $firstName,
        public ?string $lastName,
        public ?string $email,
        public ?string $phone,
        public array $experiences,
        public array $educations,
        /** @property array<int,string> $skills */
        public array $skills,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['firstName'] ?? null,
            lastName: $data['lastName'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            experiences: $data['experiences'] ?? [],
            educations: $data['educations'] ?? [],
            skills: $data['skills'] ?? [],
        );
    }
}