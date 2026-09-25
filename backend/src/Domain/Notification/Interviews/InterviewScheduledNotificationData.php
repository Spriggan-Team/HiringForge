<?php

namespace App\Domain\Notification\Interviews;

final  class InterviewScheduledNotificationData extends BaseInterviewNotificationData
{
    public function __construct(
        string $jobId,
        string $jobTitle,
        public string $interviewId,
        public \DateTimeImmutable $scheduledAt,
        public ?string $locationOrLink = null,
        public ?string $recruiterName = null,
    ) {
        parent::__construct(
            $jobId,
            $jobTitle,
            $interviewId,
            $scheduledAt,
            $locationOrLink,
            $recruiterName,
        );
    }

    public function toArray(): array
    {
        return [
            ...parent::toArray(),
        ];
    }


}