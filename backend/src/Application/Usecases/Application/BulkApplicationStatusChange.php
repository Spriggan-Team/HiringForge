<?php

namespace App\Application\Usecases\Application;

use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\Notification\JobApplicationStatusShiftData;

use App\Domain\Notification\Notification;
use App\Domain\Notification\NotificationRepositoryInterface;
use App\Domain\Notification\NotificationType;


class BulkApplicationStatusChange
{
    public function __construct(
        private ApplicationRepositoryInterface $applicationRepository,
        private NotificationRepositoryInterface $notificationRepo
    ) {}

    public function execute(string $userId, array $ids, JobApplicationStatus $newStatus): void
    {
        // Guard against empty payload
        if (empty($ids)) {
            return;
        }

        //  Check recruiter access
        $this->applicationRepository
            ->assertRecruiterHasAccessToApplicationCollection(
                recruiterId: $userId,
                applicationIds: $ids
            );

        //  Retrieve current statuses
        $statuses = $this->applicationRepository->getStatusesByIds($ids);

        //  Validate domain business rules (transitions)
        foreach ($statuses as $applicationId => $currentStatus) {
            if (!$currentStatus->canTransitionTo($newStatus)) {
                throw new \DomainException(sprintf(
                    'Application "%s" cannot transition from "%s" to "%s".',
                    $applicationId,
                    $currentStatus->value,
                    $newStatus->value
                ));
            }
        }

        // Apply database state changes
        $this->applicationRepository->bulkChangeStatus(
            applicationIds: $ids,
            newStatus: $newStatus
        );

        //  Build and dispatch notifications post-persistence
        $applicationContext = $this->applicationRepository->getApplicationContext($ids[0]);
        if(!$applicationContext){
            return;
        }

        $notifications = [];
        foreach ($statuses as $applicationId => $currentStatus) {
            $notifications[] = Notification::create(
                accountId: $applicationContext->recruiterId,
                recipientId: $userId,
                type: NotificationType::JOB_APPLICATIONS_STATUS_SHIFT,
                data: JobApplicationStatusShiftData::create(
                    applicationId: $applicationId,
                    prevStatus: $currentStatus,
                    nextStatus: $newStatus
                ),
            );
        }

        $this->notificationRepo->saveAll($notifications);
    }
}