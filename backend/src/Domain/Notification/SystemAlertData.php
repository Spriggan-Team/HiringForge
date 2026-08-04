<?php

namespace App\Domain\Notification;

final readonly class SystemAlertData implements NotificationDataInterface
{
    public function __construct(
        public string $title,
        public string $message,
        public string $level = 'info', // 'info', 'warning', 'danger'
        public ?array $metadata = null,
    ) {}

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'message' => $this->message,
            'level' => $this->level,
            'metadata' => $this->metadata,
        ], static fn ($value) => $value !== null);
    }
}