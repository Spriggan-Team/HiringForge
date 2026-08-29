<?php

namespace App\Application\Usecases\Account\JobOffer;

use App\Application\Query\JobOffer\Repositories\RecruiterJobOfferQueryRepositoryInterface;

class PublicJobOfferReader
{
    public function __construct(private RecruiterJobOfferQueryRepositoryInterface $repository){}

    public function execute(string $offerId)
    {
        $offer = $this->repository->fetchJobOfferViewById(offerId: $offerId);
        return $offer;
    }
}