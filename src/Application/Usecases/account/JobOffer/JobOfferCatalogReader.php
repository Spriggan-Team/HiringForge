<?php

namespace App\Application\Usecases\Account\JobOffer;

use App\Application\DTO\JobOffer\GetJobOfferCollectiontRequest;
use App\Application\Query\JobOffer\JobOfferQueryRepositoryInterace;

class JobOfferCatalogReader
{

    public function __construct(
        private JobOfferQueryRepositoryInterace $repository
    ){}

    public function execute(
        GetJobOfferCollectiontRequest $query
    ): array
    {
        $data = $this->repository->fetchJobOfferViewCollection(
            limit: $query->limit,
            skip: $query->skip
        );

        return $data;
    }
}