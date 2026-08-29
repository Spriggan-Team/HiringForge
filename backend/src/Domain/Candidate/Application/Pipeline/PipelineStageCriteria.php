<?php

namespace App\Domain\Candidate\Application\Pipeline;

use App\Domain\Interviews\InterviewStatus;
use App\Domain\Interviews\InterviewType;


final readonly class PipelineStageCriteria
{
    /**
     * @param array<int, > $appStatuses
     */
    public function __construct(
        public array $appStatuses,
        public ?InterviewStatus $interviewStatus = null,
        public ?InterviewType $interviewType = null,
    ) {}

    /**
     * Détermine wether this step need to filter interviews
     */
    public function hasInterviewFilter(): bool
    {
        return $this->interviewStatus !== null || $this->interviewType !== null;
    }
}