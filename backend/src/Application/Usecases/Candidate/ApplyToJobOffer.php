<?php

namespace App\Application\Usecases\Candidate;

use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\Candidate\CandidateRepositoryInterface;
use App\Domain\Shared\Account\AccountRepositoryInterface;


class ApplyToJobOffer
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private CandidateRepositoryInterface $candidateRepository,
        private ApplicationRepositoryInterface $applicationRepository
    ){}

    /**
     * @param string $candidateId The is the candidate's id
     * @param string $offerId The is  the id of an job - offer
     * @throws RessourceNotFound|DomainException
     */
    public function execute(string $candidateId, string $offerId)
    {
        $this->applicationRepository->assertExists($offerId);
        $valid = $this->candidateRepository->getCVFile($candidateId);
        if($valid)
        {
            $this->candidateRepository->apply($candidateId, $offerId);
            //-- following script...
        }
    }
}