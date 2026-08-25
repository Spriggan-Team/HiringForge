<?php

namespace App\Domain\Notification;

use App\Domain\Candidate\Application\JobApplicationStatus;

class JobApplicationStatusShiftData implements  NotificationDataInterface
{
    public function __construct(
        private string $applicationId,
        private JobApplicationStatus $prevStatus,
        private JobApplicationStatus $nextStatus
    ){}

    public static function create(
        string $applicationId,
        JobApplicationStatus $prevStatus,
        JobApplicationStatus $nextStatus,
    ): self{
        return new self(
            applicationId: $applicationId,
            prevStatus: $prevStatus,
            nextStatus: $nextStatus,
        );
    }

    public function toArray(): array{
        return [
            "applicationId" => $this->applicationId,
            "prevStatus" => $this->prevStatus->value,
            "nextStatus" => $this->nextStatus->value,
        ];
    }
}