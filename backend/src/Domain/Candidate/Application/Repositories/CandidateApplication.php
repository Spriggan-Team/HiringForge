<?php



namespace App\Domain\Candidate\Application\Repositories;


final readonly class CandidateApplication
{
    public function __construct(
        public string $applicationId,
        public string $candidateId,
        public string $firstName,
        public string $lastName,
        public string $jobOfferId,
        public string $email,
        public string $jobTitle,
        public ?string $jobImageUrl = null,
    ) {
    }
}