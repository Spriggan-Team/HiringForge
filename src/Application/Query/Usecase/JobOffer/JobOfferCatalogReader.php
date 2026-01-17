<?php

namespace App\Application\Query\Usecase\JobOffer;


use App\Api\DTO\JobOffer\GetJobOfferCollectiontRequest;
use App\Api\DTO\JobOffer\JobOffertResponse; 

use App\Domain\Repositories\JobOfferRepositioryInterface;


class JobOfferCatalogReader
{

    public function __construct(private JobOfferRepositioryInterface $repository){}

    public function execute(GetJobOfferCollectiontRequest $query): array
    {
        $data = $this->repository->getAll($query->accountId);

        for($i = 0; $i < count($data); $i++){
            $data[$i] = new JobOffertResponse(
                id: $data[$i]->id(),
                title: $data[$i]->title(),
                content: $data[$i]->content(),
                createdAt: $data[$i]->createdAt(),
                updatedAt: $data[$i]->updatedAt(),
            );
        }
        
        return $data;
    }

}