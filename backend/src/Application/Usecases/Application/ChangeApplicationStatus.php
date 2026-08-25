<?php

namespace App\Application\Usecases\Application;

use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\Notification\JobApplicationStatusShiftData;
use App\Domain\Notification\Notification;
use App\Domain\Notification\NotificationRepositoryInterface;
use App\Domain\Notification\NotificationType;

class ChangeApplicationStatus
{
    public function __construct(
        private ApplicationRepositoryInterface $applicationRepository,
        private NotificationRepositoryInterface $notificationRepo
    )
    {}

    public function execute(string $userId, string $applicationId, JobApplicationStatus $newStatus): void
    {
        // Check recruiter access
        $this->applicationRepository->assertRecruiterHasAccessToApplication(
            recruiterId: $userId,
            applicationId: $applicationId
        );

        //  Retrieve current status
        $currentStatus = $this->applicationRepository->getApplicationStatus(applicationId: $applicationId);

        //  Validate transition with explicit DomainException message
        if (!$currentStatus->canTransitionTo($newStatus)) {
            throw new \DomainException(sprintf(
                'Application "%s" cannot transition from "%s" to "%s".',
                $applicationId,
                $currentStatus->value,
                $newStatus->value
            ));
        }

        // Update status in database
        $this->applicationRepository->changeStatus(
            applicationId: $applicationId, 
            newStatus: $newStatus
        );

        //  Build and save notification
        $applicationIdentity = $this->applicationRepository->getApplicationIdentity($applicationId);

        if(!$applicationIdentity){
            return;
        }

        $notification = Notification::create(
            accountId: $applicationIdentity['recruiterId'],
            recipientId: $userId,
            type: NotificationType::JOB_APPLICATIONS_STATUS_SHIFT,
            data: JobApplicationStatusShiftData::create(
                applicationId: $applicationId,
                prevStatus: $currentStatus,
                nextStatus: $newStatus
            )
        );

        $this->notificationRepo->save($notification);
    }
}