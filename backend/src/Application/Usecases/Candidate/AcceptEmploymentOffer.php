<?php


namespace App\Application\Usecases\Candidate;

use App\Domain\EmploymentOffer\EmploymentOfferRepositoryInterface;

class AcceptEmploymentOffer
{
    public function __construct(
        private EmploymentOfferRepositoryInterface $employmentOfferRepository
    ){}

    public function execute(string $candidateId, string $employmentOfferId): void
    {
        $this->employmentOfferRepository->assertCandidateAccess(
            candidateId: $candidateId,
            employmentOfferId: $employmentOfferId
        );

        $employment = $this->employmentOfferRepository->findById($employmentOfferId);

        if ($employment === null) {
            throw new \DomainException('Employment offer not found.');
        }

        // -- No active offer
        $this->employmentOfferRepository->assertNoActiveAcceptedOffer(
            candidateId: $candidateId,
            exceptEmploymentOfferId: $employmentOfferId
        );

        // Acceptable
        $employment->markAsAccepted();

        $this->employmentOfferRepository->accept(
            candidateId: $candidateId,
            employment: $employment
        );
    }
}