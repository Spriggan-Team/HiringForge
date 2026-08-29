<?php

namespace App\Domain\Candidate\Application\Pipeline;

use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\Interviews\InterviewType;

enum PipelineStageType: string
{
    case APPLIED = JobApplicationStatus::APPLIED->value;
    case RH_INTERVIEWS = InterviewType::RH_INTERVIEWS->value;
    case TECHNICAL_INTERVIEWS = InterviewType::TECHNICAL_INTERVIEWS->value;
    case HIRED = JobApplicationStatus::HIRED->value;
}