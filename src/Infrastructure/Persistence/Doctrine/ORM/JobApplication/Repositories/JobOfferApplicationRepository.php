<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobApplication\Repositories;

use App\Domain\JobOfferApplication\Repositories\JobOfferApplicationRepositoryInterface;

class JobOfferApplicationRepository implements JobOfferApplicationRepositoryInterface
{
    public function assertExists(string $id): void
    {
        throw new \Exception('Not implemented');
    }
}