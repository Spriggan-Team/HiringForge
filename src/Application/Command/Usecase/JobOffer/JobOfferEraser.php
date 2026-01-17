<?php

namespace App\Application\Command\Usecase\JobOffer;


use App\Api\DTO\JobOffer\DeleteJobOfferRequest;
use App\Domain\Repositories\JobOfferRepositioryInterface;


class JobOfferEraser
{
    public function __construct(private JobOfferRepositioryInterface $repository){}

    public function execute(DeleteJobOfferRequest $command): void
    {
        $this->repository->delete($command->accountId, $command->uuid );
    }
}