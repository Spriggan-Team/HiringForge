<?php


namespace App\Domain\Interviews;

/**
 * Describe interviews relative data.
 * Provide with minimal object for interview information
 * Contextual information associated with an interview.
 */
final readonly class InterviewContext
{
    public function __construct(
        public string $interviewId,
        public string $jobId,
        public string $jobTitle,
        public string $candidateId
    ) {}
}