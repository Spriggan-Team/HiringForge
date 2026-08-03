<?php

namespace App\Domain\Notification;


interface NotificationRepositoryInterface
{
    /**
     * Retrieves the most recent notifications for a user.
     *
     * Results can be filtered by notification type and limited in number.
     * The returned fields are controlled by the provided projection scheme.
     *
     * @param string $userId User identifier.
     * @param NotificationType $type Notification type to retrieve.
     * @param int $limit Maximum number of notifications to return.
     * @param array{
     *     id?: bool,
     *     type?: bool,
     *     targetUrl?: bool,
     *     data?: bool,
     *     account?: array{
     *         id?: bool,
     *         firstName?: bool,
     *         lastName?: bool
     *     },
     *     readAt?: bool,
     *     isRead?: bool,
     *     createdAt?: bool
     * } $scheme Fields to fetch from persistence.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRecent(
        string $userId,
        NotificationType $type,
        ?int $limit = 7,
        array $scheme = ['id' => true],
    ): array;
}