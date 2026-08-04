<?php

namespace App\Domain\Notification;

final readonly class JobAppliedData implements NotificationDataInterface
{
    public function __construct(
        public string $jobId,
        public string $jobTitle,
        public string $candidateId,
        public string $candidateName,
        public ?string $companyId = null,
    ) {}

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
}