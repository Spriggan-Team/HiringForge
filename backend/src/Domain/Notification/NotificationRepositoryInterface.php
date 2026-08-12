<?php

namespace App\Domain\Notification;


interface NotificationRepositoryInterface
{

    /**
     * Counts the number of unread notifications for a given target (Account or Company).
     *
     * @param string $recipientId Identifier of the target (Account UUID or Company UUID).
     * @param RecipientType $recipientType 'ACCOUNT' or 'COMPANY'
     *
     * @return int Number of unread notifications.
     */
    public function countUnreadForTarget(
        string $recipientId,
        RecipientType $recipientType = RecipientType::ACCOUNT
    ): int;

    /**
     * Retrieves all notifications related to a specified recruiter (user) for a given job.
     * Checks whether the notification belongs to the user and is related to the specific jobId.
     *
     * @param string $userId The recruiter (recipient) account UUID.
     * @param string $jobId The job offer identifier.
     * @param int $limit Maximum number of notifications to return.
     *
     * @return array<int, array{
     *     id: string,
     *     targetUrl: string|null,
     *     isRead: bool,
     *     data: array,
     *     account: array{
     *         id: string|null,
     *         firstName: string,
     *         lastName: string
     *     }|null,
     *     type: NotificationType,
     *     readAt: \DateTimeImmutable|null,
     *     createdAt: \DateTimeImmutable
     * }>
     */
    public function getJobOfferNotification(string $userId, string $jobId, int $limit = 7): array;

    /**
     * Retrieves recent notifications for a given recipient (Account or Company).
     *
     * @param string $recipientId Identifier of the recipient (Account UUID or Company UUID).
     * @param string $recipientType 'ACCOUNT' or 'COMPANY'
     * @param NotificationType|null $type Notification type to retrieve.
     * @param int|null $limit Maximum number of notifications to return.
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
     *     recipient?: array{
     *         id?: bool,
     *         name?: bool,       // Enriched for Company
     *         firstName?: bool,  // Enriched for Account
     *         lastName?: bool    // Enriched for Account
     *     },
     *     readAt?: bool,
     *     isRead?: bool,
     *     createdAt?: bool
     * } $scheme Fields to fetch from persistence.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRecentForTarget(
        string $recipientId,
        RecipientType $recipientType = RecipientType::ACCOUNT,
        ?NotificationType $type = null,
        ?int $limit = 7,
        array $scheme = ['id' => true],
    ): array;

    
}