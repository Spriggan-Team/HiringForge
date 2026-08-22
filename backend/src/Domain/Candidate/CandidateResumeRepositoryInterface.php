<?php


namespace App\Domain\Candidate;

use App\Domain\File\StaticMedia;

interface CandidateResumeRepositoryInterface
{
    /**
     * Check if such a resume has already
     * been analyzd by the system
     */
    public function exists(string $candidateId, float $fileId): bool;


    
    public function hasBeenAnalyzed(
        string $candidateId,
        string $fileId
    ): bool;

    /**
     * @return string resume id
     */
    public function save(CandidateResume $candidateResume): string;

    /**
     * check with file
     */
    public function findCandidateResumeIdByFileId(string $fileId): ?string;

    /**
     * Return candidate resume
     */
    public function getResumeFile(string $candidateId): ?StaticMedia;

    /**
     * Mark a resume as parsed
     */
    public function markAsParsed(string $candidateResumeId): void;
}