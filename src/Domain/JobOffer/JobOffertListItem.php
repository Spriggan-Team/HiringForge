<?php

namespace App\Domain\JobOffer;

/**
 * An object that only pupose is to shaped the return value of a domain event (ex: repository)
 */
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