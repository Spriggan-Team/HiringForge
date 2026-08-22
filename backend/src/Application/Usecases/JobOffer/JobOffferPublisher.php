<?php

namespace App\Application\Usecases\JobOffer;

use App\Domain\JobOffer\JobOfferRepositoryInterface;

class JobOffferPublisher
{
    public function __construct(
        private JobOfferRepositoryInterface $repository,
    ){}

    /**
     * @param string $userId the id of the concerned user
     * @param string $offerId the id of the concerned job
     */
    public function execute(string $accountId, string $offerId)
    {
        $this->repository->publish($offerId, $accountId);
    }
}