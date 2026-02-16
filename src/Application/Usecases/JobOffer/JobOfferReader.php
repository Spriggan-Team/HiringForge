<?php

namespace App\Application\Query\Usecase\JobOffer;

use App\Domain\JobOffer\JobOfferRepositioryInterface;

class JobOfferReader
{
    public function __construct(private JobOfferRepositioryInterface $repository){}

    public function execute(string $offerId)
    {
        $offer = $this->repository->fetchJobOfferViewById($offerId);
        return $offer;
    }
}