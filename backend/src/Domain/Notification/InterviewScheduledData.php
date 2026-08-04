<?php

namespace App\Domain\Notification;

final  class InterviewScheduledData extends BaseJobNotificationData implements NotificationDataInterface
{
    public function __construct(
        string $jobId,
        string $jobTitle,
        public \DateTimeImmutable $scheduledAt,
        public ?string $locationOrLink = null,
        public ?string $recruiterName = null,
    ) {
        parent::__construct($jobId, $jobTitle);
    }

    public function toArray(): array
    {
        return array_filter([
            'jobTitle' => $this->jobTitle,
            'scheduledAt' => $this->scheduledAt->format(\DateTimeInterface::ATOM),
            'locationOrLink' => $this->locationOrLink,
            'recruiterName' => $this->recruiterName,
        ], static fn ($value) => $value !== null);
    }

    
    public function getJobId(): string { return $this->jobId; }
    public function getJobTitle(): string { return $this->jobTitle; }
}