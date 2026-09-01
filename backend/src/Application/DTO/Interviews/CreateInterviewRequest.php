<?php


namespace App\Application\DTO\Interviews;

use App\Domain\Interviews\InterviewType;

class CreateInterviewRequest
{
    public function __construct(
        public readonly string $candidateId,
        public readonly string $applicationId,

        public readonly ?string $title,
        public readonly ?string $description,
        
        public readonly ?InterviewType $type,
        public readonly ?string $url,
        public readonly ?int $minutes,
        
        public readonly \DateTimeImmutable $scheduledAt,

    ) {}
}