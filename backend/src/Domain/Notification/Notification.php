<?php

namespace App\Domain\Notification;

use DateTimeImmutable;


final class Notification
{
    private function __construct(
        private readonly string $id,
        private readonly string $accountId,
        private readonly NotificationType $type,
        private ?string $targetUrl,
        private array $data,
        private bool $isRead,
        private ?DateTimeImmutable $readAt,
        private readonly DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        string $id,
        string $accountId,
        NotificationType $type,
        ?string $targetUrl = null,
        array $data = [],
    ): self {
        return new self(
            id: $id,
            accountId: $accountId,
            type: $type,
            targetUrl: $targetUrl,
            data: $data,
            isRead: false,
            readAt: null,
            createdAt: new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        string $id,
        string $accountId,
        NotificationType $type,
        ?string $targetUrl,
        array $data,
        bool $isRead,
        ?DateTimeImmutable $readAt,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            id: $id,
            accountId: $accountId,
            type: $type,
            targetUrl: $targetUrl,
            data: $data,
            isRead: $isRead,
            readAt: $readAt,
            createdAt: $createdAt,
        );
    }

    public function markAsRead(): void
    {
        if ($this->isRead) {
            return;
        }

        $this->isRead = true;
        $this->readAt = new DateTimeImmutable();
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function accountId(): string
    {
        return $this->accountId;
    }

    public function type(): NotificationType
    {
        return $this->type;
    }

    public function targetUrl(): ?string
    {
        return $this->targetUrl;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function readAt(): ?DateTimeImmutable
    {
        return $this->readAt;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}