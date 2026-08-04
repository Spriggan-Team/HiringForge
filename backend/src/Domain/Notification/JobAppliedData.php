<?php

namespace App\Domain\Notification;

final class JobAppliedData extends BaseJobNotificationData implements NotificationDataInterface
{
    public function __construct(
        string $jobId,
        string $jobTitle,
        public string $candidateId,
        public string $candidateName,
        public ?string $companyId = null,
    ) {
        parent::__construct($jobId, $jobTitle);
    }

    public function toArray(): array
    {
        return array_filter([
            'jobId' => $this->jobId,
            'jobTitle' => $this->jobTitle,
            'candidateId' => $this->candidateId,
            'candidateName' => $this->candidateName,
            'companyId' => $this->companyId,
        ], static fn ($value) => $value !== null);
    }

    public function getJobId(): string { return $this->jobId; }
    public function getJobTitle(): string { return $this->jobTitle; }
}