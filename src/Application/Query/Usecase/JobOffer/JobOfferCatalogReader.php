<?php

namespace App\Application\Query\Usecase\JobOffer;


use App\Api\DTO\JobOffer\GetJobOfferCollectiontRequest;
use App\Api\DTO\JobOffer\JobOffertResponse;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\JobOfferRepository;


class JobOfferCatalogReader
{

    public function __construct(private JobOfferRepository $repository){}

    public function execute(GetJobOfferCollectiontRequest $query): array
    {
        $data = $this->repository->getAll($query->accountId);

        for($i = 0; $i < count($data); $i++){
            $data[$i] = new JobOffertResponse(
                id: $data[$i]->getId(),
                title: $data[$i]->getTitle(),
                content: $data[$i]->getContent(),
                createdAt: $data[$i]->getCreatedAt(),
                updatedAt: $data[$i]->getUpdatedAt(),
            );
        }
        
        return $data;
    }

}