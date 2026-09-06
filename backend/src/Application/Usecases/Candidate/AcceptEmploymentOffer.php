<?php


namespace App\Application\Usecases\Candidate;

use App\Domain\EmploymentOffer\EmploymentOfferRepositoryInterface;

class AcceptEmploymentOffer
{
    public function __construct(
        private EmploymentOfferRepositoryInterface $employmentOfferRepository
    ){}

    public function execute(string $candidateId, string $employmentOfferId){
        $this->employmentOfferRepository->assertCandidateAccess(
            candidateId: $candidateId,
            employmentOfferId: $employmentOfferId
        );

        $employment = $this->employmentOfferRepository->findById($employmentOfferId);
        if($employment){
            throw new \DomainException("No found employment");
        }
    }
}