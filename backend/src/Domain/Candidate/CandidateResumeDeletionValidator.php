<?php

namespace App\Domain\Candidate;

use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\Exception\ResumeDeletionNotAllowedException;

final readonly class CandidateResumeDeletionValidator
{
    public function __construct(
        private ApplicationRepositoryInterface $applicationRepo
    ) {}

    /**
     * @throws ResumeDeletionNotAllowedException
     */
    public function assertCanBeDeleted(string $candidateId, string $resumeId): void
    {
        // Verify if cv/resume id disponible
        $hasActiveApplications = $this->applicationRepo->hasApplicationsUsingResume(
            candidateId: $candidateId,
            resumeId: $resumeId
        );

        if ($hasActiveApplications)
        {
            throw new ResumeDeletionNotAllowedException(
                "Cannot delete resume because it is linked to one or more job applications."
            );
        }
    }
}