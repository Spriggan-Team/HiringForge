<?php

namespace App\Domain\Interviews;

class Interview
{
    private ?string $id = null;

    private \DateTimeImmutable $startDate;

    private ?string $title = null;

    private int $minutes;

    private ?string $description = null;

    private ?string $url = null;

    private InterviewStatus $status;

    private bool $candidateApproval = false;

    private ?string $rejectionReason = null;

    private ?InterviewType $type = null;

    /**
     * Relations represented by IDs in the Domain.
     */
    private string $applicationId;

    private string $userId;


    private function __construct(
        int $minutes,
        \DateTimeImmutable $startDate,
        string $applicationId,
        string $userId,

        InterviewStatus $status = InterviewStatus::SCHEDULED,
        ?string $id = null,
        ?string $title = null,
        ?string $description = null,
        ?string $url = null,
        ?InterviewType $type = null,
        bool $candidateApproval = false,
        ?string $rejectionReason = null,
    ) {
        if ($minutes <= 0) {
            throw new \InvalidArgumentException(
                'Interview duration must be greater than zero.'
            );
        }

        $this->id = $id;
        $this->minutes = $minutes;
        $this->startDate = $startDate;
        $this->applicationId = $applicationId;
        $this->userId = $userId;

        $this->status = $status;
        $this->title = $title;
        $this->description = $description;
        $this->url = $url;
        $this->type = $type;
        $this->candidateApproval = $candidateApproval;
        $this->rejectionReason = $rejectionReason;
    }


    // ==========================================
    // Factory
    // ==========================================

    public static function create(
        int $minutes,
        \DateTimeImmutable $startDate,
        string $applicationId,
        string $userId,

        ?string $title = null,
        ?string $description = null,
        ?string $url = null,
        ?InterviewType $type = null,
        ?string $id = null,
    ): self {
        return new self(
            minutes: $minutes,
            startDate: $startDate,
            applicationId: $applicationId,
            userId: $userId,

            status: InterviewStatus::SCHEDULED,
            id: $id,
            title: $title,
            description: $description,
            url: $url,
            type: $type,
        );
    }


    // ==========================================
    // Getters
    // ==========================================

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getMinutes(): int
    {
        return $this->minutes;
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getStatus(): InterviewStatus
    {
        return $this->status;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getType(): ?InterviewType
    {
        return $this->type;
    }

    public function getApplicationId(): string
    {
        return $this->applicationId;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function isCandidateApproved(): bool
    {
        return $this->candidateApproval;
    }

    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }


    // ==========================================
    // Business methods
    // ==========================================

    public function accept(): void
    {
        $this->candidateApproval = true;
        $this->rejectionReason = null;
    }


    public function reject(string $reason): void
    {
        $reason = trim($reason);

        if ($reason === '') {
            throw new \InvalidArgumentException(
                'A valid reason is required to decline an interview.'
            );
        }

        $this->candidateApproval = false;
        $this->rejectionReason = $reason;
        $this->status = InterviewStatus::CLOSED;
    }


    public function cancel(): void
    {
        $this->status = InterviewStatus::CLOSED;
    }


    // ==========================================
    // Update methods
    // ==========================================

    public function reschedule(
        \DateTimeImmutable $startDate
    ): void {
        $this->startDate = $startDate;
    }


    public function changeDuration(
        int $minutes
    ): void {
        if ($minutes <= 0) {
            throw new \InvalidArgumentException(
                'Interview duration must be greater than zero.'
            );
        }

        $this->minutes = $minutes;
    }
}
