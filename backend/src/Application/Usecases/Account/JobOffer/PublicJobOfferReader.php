<?php

namespace App\Application\Usecases\Account\JobOffer;

use App\Application\Query\JobOffer\JobOfferQueryRepositoryInterface;


class PublicJobOfferReader
{
    public function __construct(private JobOfferQueryRepositoryInterface $repository){}

    public function execute(string $offerId)
    {
        $offer = $this->repository->fetchJobOfferViewById(offerId: $offerId);
        return $offer;
    }
}