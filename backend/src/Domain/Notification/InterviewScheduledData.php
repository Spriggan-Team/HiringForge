<?php

namespace App\Domain\Notification;

final readonly class InterviewScheduledData implements NotificationDataInterface
{
    public function __construct(
        public string $jobTitle,
        public \DateTimeImmutable $scheduledAt,
        public ?string $locationOrLink = null,
        public ?string $recruiterName = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'jobTitle' => $this->jobTitle,
            'scheduledAt' => $this->scheduledAt->format(\DateTimeInterface::ATOM),
            'locationOrLink' => $this->locationOrLink,
            'recruiterName' => $this->recruiterName,
        ], static fn ($value) => $value !== null);
    }
}