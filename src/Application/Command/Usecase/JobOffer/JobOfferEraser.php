<?php

namespace App\Application\Command\Usecase\JobOffer;


use App\Api\DTO\JobOffer\DeleteJobOfferRequest;
use App\Infrastructure\Persistence\Doctrine\ORM\Repositories\JobOfferRepository;


class JobOfferEraser
{
    public function __construct(private JobOfferRepository $repository){}

    public function execute(DeleteJobOfferRequest $command): void
    {
        $this->repository->delete($command->accountId, $command->uuid );
    }
}