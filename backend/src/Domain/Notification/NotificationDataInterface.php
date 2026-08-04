<?php

namespace App\Domain\Notification;


interface NotificationDataInterface
{
    /**
     * Convert the Data Object to an array for JSON persistence.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}