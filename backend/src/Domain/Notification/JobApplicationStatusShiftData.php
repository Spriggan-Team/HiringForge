<?php

namespace App\Domain\Notification;

use App\Domain\Candidate\Application\JobApplicationStatus;

class JobApplicationStatusShiftData implements  NotificationDataInterface
{
    public function __construct(
        private string $jobTitle,
        private string $applicationId,
        private JobApplicationStatus $prevStatus,
        private JobApplicationStatus $nextStatus,
    ){}

    public static function create(
        string $jobTitle,
        string $applicationId,
        JobApplicationStatus $prevStatus,
        JobApplicationStatus $nextStatus,
    ): self{
        return new self(
            jobTitle: $jobTitle,
            applicationId: $applicationId,
            prevStatus: $prevStatus,
            nextStatus: $nextStatus,
        );
    }

    public function toArray(): array{
        return [
            "jobTitle" => $this->jobTitle,
            "applicationId" => $this->applicationId,
            "prevStatus" => $this->prevStatus->value,
            "nextStatus" => $this->nextStatus->value,
        ];
    }
}