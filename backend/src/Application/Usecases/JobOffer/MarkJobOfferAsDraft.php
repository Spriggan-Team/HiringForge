<?php

namespace App\Application\Usecases\JobOffer;

use App\Domain\JobOffer\JobOfferRepositoryInterface;

class MarkJobOfferAsDraft
{
    public function __construct(
        private JobOfferRepositoryInterface $jobOfferRepositoryInterface
    ){}

    /**
     * This function help you turn back a joboffer into a draft
     * @param string $accountId   -   the id of the current user/actor
     * @param string $offerId     -   the related job
     */
    public function execute(string $accountId,string $offerId)
    {

    }
}