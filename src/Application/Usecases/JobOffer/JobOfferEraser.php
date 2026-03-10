<?php

namespace App\Application\Usecases\JobOffer;


use App\Api\DTO\JobOffer\DeleteJobOfferRequest;
use App\Domain\JobOffer\JobOfferRepositioryInterface;


class JobOfferEraser
{
    public function __construct(private JobOfferRepositioryInterface $repository){}

    public function execute(string $offerId, string $accountId): void
    {
        $this->repository->delete($offerId,  $accountId);
    }
}