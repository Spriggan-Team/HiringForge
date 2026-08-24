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


    /**
     * Count candidate saved resume
     */
    public function countResume(string $candidateId): int;
    
    

    /**
     * Has been analyzed
     */
    public function hasBeenAnalyzed(
        string $candidateId,
        string $fileId
    ): bool;

    /**
     * @return string resume id
     */
    public function save(CandidateResume $candidateResume): string;

    /**
     * Cascade removal on file 
     */
    public function delete(string $candidateId, string $fileId): void;

    /**
     * check with file
     */
    public function findCandidateResumeIdByFileId(string $fileId): ?string;
    

    /**
     * Find one or null candiodate resume
     */
    public function findOneOrNull(string $candidateId, string $fileId): ?CandidateResume;

    /**
     * Returns a candidate's special resume file.
     */
    public function findResumeMediaOrNull(string $candidateId, string $fileId): ?StaticMedia;


    /**
     * Return candidate resumes
     * @return array<int, StaticMedia>
     */
    public function getResumeFiles(string $candidateId): array;

    /**
     * Mark a resume as parsed
     */
    public function markAsParsed(string $candidateResumeId): void;

}