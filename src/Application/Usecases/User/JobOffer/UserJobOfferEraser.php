<?php

namespace App\Application\Usecases\User\JobOffer;


use App\Api\DTO\JobOffer\DeleteJobOfferRequest;
use App\Domain\JobOffer\JobOfferRepositioryInterface;


class UserJobOfferEraser
{
    public function __construct(private JobOfferRepositioryInterface $repository){}

    public function execute(string $offerId, string $accountId): void
    {
        $this->repository->delete($offerId,  $accountId);
    }
}