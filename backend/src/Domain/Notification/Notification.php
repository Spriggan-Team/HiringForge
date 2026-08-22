<?php

namespace App\Domain\Notification;

use DateTimeImmutable;


final class Notification
{
    private function __construct(
        private readonly string $accountId,
        private readonly NotificationType $type,
        private NotificationDataInterface $data,
        private bool $isRead,
        private ?DateTimeImmutable $readAt,
        private readonly DateTimeImmutable $createdAt,
        private ?string $id = null,
        private ?string $recipientId =null, 
        private ?string $targetUrl =null,
        private ?string $recipientCompanyId = null,
    ) {
    }

    public static function create(
        string $accountId,
        NotificationType $type,
        NotificationDataInterface $data,
        ?string $recipientCompanyId =null,
        ?string $id =null,
        ?string $recipientId = null,
        ?string $targetUrl = null,
    ): self {
        return new self(
            id: $id,
            accountId: $accountId,
            type: $type,
            targetUrl: $targetUrl,
            data: $data,
            recipientCompanyId: $recipientCompanyId,
            isRead: false,
            readAt: null,
            recipientId: $recipientId,
            createdAt: new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        string $id,
        string $accountId,
        NotificationType $type,
        ?string $targetUrl,
        NotificationDataInterface $data,
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

    public function recipientId()
    {
        return $this->recipientId;
    }

    public function recipientCompanyId(){
        return $this->recipientCompanyId;
    }


    public function type(): NotificationType
    {
        return $this->type;
    }

    public function targetUrl(): ?string
    {
        return $this->targetUrl;
    }

    public function data(): NotificationDataInterface
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

    //------------------
    //--- SETTERS
    //------------------------
    public function  setId(?string $id)
    {
        $this->id = $id;
        return $this;
    }
}