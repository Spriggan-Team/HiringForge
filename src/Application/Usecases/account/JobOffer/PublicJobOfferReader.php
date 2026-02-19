<?php

namespace App\Application\Usecases\Account\JobOffer;

use App\Application\Query\JobOffer\JobOfferQueryRepositoryInterace;


class PublicJobOfferReader
{
    public function __construct(private JobOfferQueryRepositoryInterace $repository){}

    public function execute(string $offerId)
    {
        $offer = $this->repository->fetchJobOfferViewById($offerId);
        return $offer;
    }
}