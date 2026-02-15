<?php

namespace App\Application\Command\Usecase\JobOffer;

use App\Domain\JobOffer\JobOfferRepositioryInterface;

class JobOffferPublisher
{
    public function __construct(
        private JobOfferRepositioryInterface $repository,
    ){}

    /**
     * @param string $userId the id of the concerned user
     * @param string $offerId the id of the concerned job
     */
    public function execute(string $userId, string $offerId)
    {
        $this->repository->publish($offerId, $userId);
    }
}