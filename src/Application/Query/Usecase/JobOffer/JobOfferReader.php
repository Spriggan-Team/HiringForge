<?php

namespace App\Application\Query\Usecase\JobOffer;

use App\Api\DTO\JobOffer\GetJobOfferRequest;
use App\Api\DTO\JobOffer\JobOffertResponse;
use App\Domain\Repositories\JobOfferRepositioryInterface;

class JobOfferReader
{
    public function __construct(private JobOfferRepositioryInterface $repository){}

    public function execute(GetJobOfferRequest $query): JobOffertResponse
    {
        $offer = $this->repository->getById($query->accountId, $query->uuid);
        $apiResponse =  new JobOffertResponse(
                            id: $offer->id(),
                            title: $offer->title(),
                            content: $offer->content(),
                            createdAt: $offer->createdAt(),
                            updatedAt: $offer->updatedAt()
                        );
        return $apiResponse;
    }
}