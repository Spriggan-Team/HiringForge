<?php


namespace App\Domain\Notification;


final class InterviewCancelledData extends BaseJobNotificationData implements NotificationDataInterface
{
    public function __construct(
        string $jobId,
        string $jobTitle,
        public \DateTimeImmutable $scheduledAt,
        public ?string $recruiterName = null,
        public ?string $reason = null,
    ) {
        parent::__construct($jobId, $jobTitle);
    }

    public function toArray(): array
    {
        return array_filter([
            'jobTitle' => $this->jobTitle,
            'scheduledAt' => $this->scheduledAt->format(\DateTimeInterface::ATOM),
            'recruiterName' => $this->recruiterName,
            'reason' => $this->reason,
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