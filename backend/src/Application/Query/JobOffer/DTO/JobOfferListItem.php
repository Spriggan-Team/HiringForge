<?php

namespace App\Application\Query\JobOffer\DTO;

/**
 * Lightweight projection representing a job offer in listing contexts.
 *
 * This object is a read model used to shape data returned by
 * query repositories. It is not a domain entity and contains
 * no business logic.
 */
class JobOfferListItem
{
    public function __construct(
        public string $id,
        public string $title,
        public array $content,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ){}
}