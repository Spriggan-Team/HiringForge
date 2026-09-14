<?php

namespace App\Application\Usecases\Candidate;

use App\Domain\EmploymentOffer\EmploymentOfferRepositoryInterface;

class RefuseEmployementOffer
{
    public function __construct(
        private EmploymentOfferRepositoryInterface $employmentOfferRepository
    ){}



    public function execute(
        string $candidateId,
        string $employmentOfferId,
        string $rejectionReason
    ): void {
        $this->employmentOfferRepository->assertCandidateAccess(
            candidateId: $candidateId,
            employmentOfferId: $employmentOfferId
        );

        $employment = $this->employmentOfferRepository->findById(
            $employmentOfferId
        );

        if ($employment === null) {
            throw new \DomainException('Employment offer not found.');
        }

        $employment->markAsRejected($rejectionReason);

        $this->employmentOfferRepository->refuse(
            candidateId: $candidateId,
            employment: $employment
        );
    }
}