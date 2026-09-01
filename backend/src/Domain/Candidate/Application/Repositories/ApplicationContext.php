<?php

namespace App\Domain\Candidate\Application\Repositories;

final readonly class ApplicationContext
{
    public function __construct(
        public string $applicationId,
        public string $candidateId,
        public string $jobTitle,
        public string $companyName,
        public string $recruiterId,
        public string $jobId,
    ) {}
}