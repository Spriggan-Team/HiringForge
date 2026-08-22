<?php

namespace App\Domain\Notification;

final class JobAppliedNotificationData extends BaseJobNotificationData implements NotificationDataInterface
{
    public function __construct(
        string $jobId,
        string $jobTitle,
        public string $candidateId,
        public string $candidateName,
    ) {
        parent::__construct($jobId, $jobTitle);
    }

    public static function create(
        string $jobId,
        string $jobTitle,
        string $candidateId,
        string $candidateName,
        ?string $companyId = null,
    ): self{
        return new self(
            jobId: $jobId,
            jobTitle: $jobTitle,
            candidateId: $candidateId,
            candidateName: $candidateName,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'jobId' => $this->jobId,
            'jobTitle' => $this->jobTitle,
            'candidateId' => $this->candidateId,
            'candidateName' => $this->candidateName,
        ], static fn ($value) => $value !== null);
    }

    public function getJobId(): string { return $this->jobId; }
    public function getJobTitle(): string { return $this->jobTitle; }
}