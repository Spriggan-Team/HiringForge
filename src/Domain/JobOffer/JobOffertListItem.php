<?php

namespace App\Domain\JobOffer;

class JobOffertListItem
{
    public function __construct(
        public string $id,
        public string $title,
        public array $content,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ){}
}