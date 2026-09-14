<?php

namespace App\Application\Query\JobOffer\Repositories;


interface CandidateJobOfferRepositoryInterface{
    public function hasViewed(        
        string $candidateId,
        string $jobOfferId
    ): bool;
}