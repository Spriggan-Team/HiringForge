<?php

namespace App\Application\Query\Usecase\JobOffer;

use App\Domain\JobOffer\JobOfferRepositioryInterface;

class JobOfferCatalogReader
{

    public function __construct(
        private JobOfferRepositioryInterface $repository
    ){}

    public function execute(
        ?int $skip  = null,
        ?int $limit = null,
    ): array
    {
        $data = $this->repository->getAll(
            limit: $limit,
            skip: $skip
        );
        return $data;
    }

}