<?php


namespace App\Domain\Notification\Interviews;

use App\Domain\Notification\Interviews\BaseInterviewNotificationData;

final  class InterviewDecisionNotificationData extends BaseInterviewNotificationData
{
    public function __construct(
        string $jobId,
        string $jobTitle,
        string $interviewId,
        public bool $candidateApproval,
        \DateTimeImmutable $scheduledAt,
        ?string $locationOrLink = null,
        ?string $recruiterName = null,
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
            'decision' => $this->candidateApproval,
        ];
    }

}