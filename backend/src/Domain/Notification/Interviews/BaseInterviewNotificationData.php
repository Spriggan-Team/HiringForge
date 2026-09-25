<?php

namespace App\Domain\Notification\Interviews;

use App\Domain\Notification\BaseJobNotificationData;
use App\Domain\Notification\NotificationDataInterface;

abstract class BaseInterviewNotificationData extends BaseJobNotificationData implements NotificationDataInterface
{
    public function __construct(
        string $jobId,
        string $jobTitle,
        public string $interviewId,
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
            'interviewId' => $this->interviewId,
            'scheduledAt' => $this->scheduledAt->format(\DateTimeInterface::ATOM),
            'locationOrLink' => $this->locationOrLink,
            'recruiterName' => $this->recruiterName,
        ], static fn ($value) => $value !== null);
    }

    public function getJobId(): string
    {
        return $this->jobId;
    }

    public function getJobTitle(): string
    {
        return $this->jobTitle;
    }
}