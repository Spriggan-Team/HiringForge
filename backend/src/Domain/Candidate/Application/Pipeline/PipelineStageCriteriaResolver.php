<?php

namespace App\Domain\Candidate\Application\Pipeline;

use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\Interviews\InterviewStatus;
use App\Domain\Interviews\InterviewType;


final class PipelineStageCriteriaResolver
{
    public function resolve(PipelineStageType $stageType): PipelineStageCriteria
    {
        return match ($stageType) {
            PipelineStageType::APPLIED => new PipelineStageCriteria(
                appStatuses: [JobApplicationStatus::APPLIED],
                interviewStatus: null,
                interviewType: null,
            ),
            PipelineStageType::RH_INTERVIEWS => new PipelineStageCriteria(
                appStatuses: [JobApplicationStatus::INTERVIEW_SCHEDULED, JobApplicationStatus::IN_INTERVIEW],
                interviewStatus: InterviewStatus::SCHEDULED,
                interviewType: InterviewType::RH_INTERVIEWS,
            ),
            PipelineStageType::TECHNICAL_INTERVIEWS => new PipelineStageCriteria(
                appStatuses: [JobApplicationStatus::INTERVIEW_SCHEDULED, JobApplicationStatus::IN_INTERVIEW],
                interviewStatus: InterviewStatus::SCHEDULED,
                interviewType: InterviewType::TECHNICAL_INTERVIEWS,
            ),
            PipelineStageType::HIRED => new PipelineStageCriteria(
                appStatuses: [JobApplicationStatus::HIRED],
                interviewStatus: InterviewStatus::SCHEDULED,
                interviewType: null,
            ),
        };
    }
}