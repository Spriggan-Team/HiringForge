<?php

namespace App\Api\DTO\JobOffer;

class JobOffertResponse
{
    public function __construct(
        public string $id,
        public string $title,
        public array $content,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ){}
}