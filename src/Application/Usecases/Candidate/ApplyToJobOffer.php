<?php

namespace App\Application\Usecases\Candidate;

use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\JobOfferApplication\Repositories\JobOfferApplicationRepositoryInterface;

class ApplyToJobOffer
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private CandidateRepositoryInterface $candidateRepository,
        private JobOfferApplicationRepositoryInterface $applicationRepository
    ){}

    /**
     * @param string $candidateId The is the candidate's id
     * @param string $offerId The is  the id of an job - offer
     * @throws RessourceNotFound|DomainException
     */
    public function execute(string $candidateId, string $offerId)
    {
        $identity = $this->accountRepository->exists($candidateId);
        $this->applicationRepository->assertExists($offerId);

        if($identity)
        {
            $this->candidateRepository->apply($candidateId, $offerId);
        }
    }
}