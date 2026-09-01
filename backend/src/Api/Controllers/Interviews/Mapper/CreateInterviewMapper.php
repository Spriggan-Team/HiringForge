<?php



namespace App\Api\Controllers\Interviews\Mapper;

use App\Application\DTO\Interviews\CreateInterviewRequest;
use App\Domain\Interviews\InterviewType;



class CreateInterviewMapper
{
    public function fromArray(
        array $body
    ): CreateInterviewRequest {
        return new CreateInterviewRequest(
            title: $body['title'] ?? null,
            candidateId: $body['candidateId'],
            scheduledAt: new \DateTimeImmutable(
                $body['scheduledAt']
            ),
            description: $body['description'] ?? null,
            url: $body['url'] ?? null,
            applicationId: $body['applicationId'],
            type: !empty($body['type'])
                ? InterviewType::from($body['type'])
                : null,
            minutes: isset($body['minutes'])
                && is_numeric($body['minutes'])
                    ? (int) $body['minutes']
                    : null,
        );
    }
}