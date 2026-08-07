<?php

namespace App\Application\Usecases\Offer;

use App\Application\DTO\Offer\CreateOfferRequestDto;
use App\Domain\Offer\Offer;
use App\Domain\Offer\OfferRepositoryInterface;

class CreateOffer
{
    public function __construct(
        private OfferRepositoryInterface $offerRepository
    ){}

    public function execute(string $userId, CreateOfferRequestDto $command): Offer
    {
        $expiredAt = new \DateTimeImmutable($command->expiredAt);

        $offer = Offer::create(
            title: $command->title,
            message: $command->message,
            salary: $command->salary,
            expiredAt: $expiredAt,
            candidateId: $command->candidateId,
            applicationId: $command->applicationId
        );

        $this->offerRepository->save($userId, $offer);

        return $offer;
    }
}

