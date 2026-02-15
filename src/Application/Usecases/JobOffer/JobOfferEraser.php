<?php

namespace App\Application\Command\Usecase\JobOffer;


use App\Api\DTO\JobOffer\DeleteJobOfferRequest;
use App\Domain\JobOffer\JobOfferRepositioryInterface;


class JobOfferEraser
{
    public function __construct(private JobOfferRepositioryInterface $repository){}

    public function execute(string $offerId, string $userId): void
    {
        $this->repository->delete($offerId,  $userId);
    }
}