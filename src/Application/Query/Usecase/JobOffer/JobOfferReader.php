<?php

namespace App\Application\Query\Usecase\JobOffer;

use App\Api\DTO\JobOffer\GetJobOfferRequest;
use App\Api\DTO\JobOffer\JobOffertResponse;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\JobOfferRepository;

class JobOfferReader
{
    public function __construct(private JobOfferRepository $repository){}

    public function execute(GetJobOfferRequest $query): JobOffertResponse
    {
        $offer = $this->repository->getById($query->accountId, $query->uuid);
        $apiResponse =  new JobOffertResponse(
                            id: $offer->getId(),
                            title: $offer->getTitle(),
                            content: $offer->getContent(),
                            createdAt: $offer->getCreatedAt(),
                            updatedAt: $offer->getUpdatedAt()
                        );
        return $apiResponse;
    }
}