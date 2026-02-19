<?php

namespace App\Application\Usecases\User\JobOffer;

use App\Domain\JobOffer\JobOfferRepositioryInterface;

class UserJobOffferPublisher
{
    public function __construct(
        private JobOfferRepositioryInterface $repository,
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