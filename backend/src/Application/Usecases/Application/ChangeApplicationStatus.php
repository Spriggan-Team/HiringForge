<?php

namespace App\Application\Usecases\Application;

use App\Domain\Candidate\Application\JobApplicationStatus;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\EmploymentOffer\EmploymentOfferRepositoryInterface;

use App\Domain\Exception\EmploymentOfferException;
use App\Domain\Interviews\InterviewsRepositoryInterface;
use App\Domain\Notification\JobApplicationStatusShiftData;
use App\Domain\Notification\Notification;
use App\Domain\Notification\NotificationRepositoryInterface;
use App\Domain\Notification\NotificationType;
use App\Domain\Shared\TransactionManagerInterface;


class ChangeApplicationStatus
{
    public function __construct(
        private ApplicationRepositoryInterface $applicationRepository,
        private NotificationRepositoryInterface $notificationRepo,
        private TransactionManagerInterface $transactionManager,
        private InterviewsRepositoryInterface $interviewsRepository,
        private EmploymentOfferRepositoryInterface $employmentOfferRepository
    )
    {}

    /**
     * @param string $userId - recruiterId
     * @param string $applicationId - applicationId
     * @param JobApplicationStatus $newStatus - new status
     */
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
        $this->transactionManager->execute(function () use ($userId, $applicationId, $currentStatus, $newStatus) {
            
            if ($newStatus === JobApplicationStatus::REJECTED) {
                if ($this->employmentOfferRepository->hasActiveOffer(applicationId: $applicationId)) {
                    throw new EmploymentOfferException("You cannot reject a candidate who has an active job offer. First, cancel the offer.");
                }

                $this->interviewsRepository->cancelInterviewPlansForApplication(
                    recruiterId: $userId,
                    applicationId: $applicationId
                );
            }
            
            // Change status
            $this->applicationRepository->changeStatus(
                applicationId: $applicationId, 
                newStatus: $newStatus
            );

            // Build and save notification
            $applicationContext = $this->applicationRepository->getApplicationContext($applicationId);

            if ($applicationContext) {
                $notification = Notification::create(
                    accountId: $applicationContext->recruiterId,
                    recipientId: $applicationContext->candidateId,
                    type: NotificationType::JOB_APPLICATIONS_STATUS_SHIFT,
                    data: JobApplicationStatusShiftData::create(
                        applicationId: $applicationId,
                        prevStatus: $currentStatus,
                        nextStatus: $newStatus
                    )
                );

                $this->notificationRepo->save($notification);
            }
        });
    }
}