<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\Repositories;

use App\Application\Query\JobOffer\JobOfferQueryRepositoryInterace;
use App\Application\Query\JobOffer\JobOffertListItem;

/**
 * Doctrine-based read repository dedicated to job offer queries.
 *
 * This repository is responsible only for data retrieval optimized
 * for presentation or read use cases (queries).
 * 
 * It does NOT reconstruct domain aggregates and must not contain
 * business logic. Its purpose is to return lightweight projections
 * tailored for application query handlers.
 */

class JobOfferQueryRepository implements JobOfferQueryRepositoryInterace
{
    public function fetchJobOfferViewById(string $offerId): JobOffertListItem
    {
        throw new \Exception('Not implemented');
    }

    public function fetchJobOfferViewCollection(?int $limit = null, ?int $skip = null): array
    {
        throw new \Exception('Not implemented');
    }
}