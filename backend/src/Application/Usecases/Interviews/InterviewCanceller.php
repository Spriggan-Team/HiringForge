<?php

namespace App\Application\Usecases\Interviews;

use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Exception\UnauthorizedAction;

use App\Domain\Interviews\InterviewsRepositoryInterface;
use App\Domain\JobOffer\JobOfferRepositoryInterface;

use App\Domain\Notification\InterviewCancelledData;
use App\Domain\Notification\Notification;
use App\Domain\Notification\NotificationRepositoryInterface;
use App\Domain\Notification\NotificationType;
use App\Domain\Shared\Account\AccountRepositoryInterface;
use App\Domain\Shared\TransactionManagerInterface;
use App\Domain\User\UserRepositoryInterface;

class InterviewCanceller
{
    public function __construct(
        private InterviewsRepositoryInterface $interviewRepository,
        private TransactionManagerInterface $transactionManager,
        private NotificationRepositoryInterface $notificationRepository,
        private AccountRepositoryInterface $accountRepository,
    ) {}

    public function execute(
        string $userId,
        string $interviewId
    ): void {
        //--------------------------------
        //-- Retrieve interview
        //--------------------------------

        $interview = $this->interviewRepository->findById(
            $interviewId
        );

        if ($interview === null) {
            throw new ResourceNotFoundException(
                'Interview not found.'
            );
        }

        //--------------------------------
        //-- Authorization
        //--------------------------------

        $hasAccess = $this->interviewRepository->isUserAssociatedWithInterview(
                userId: $userId,
                interviewId: $interviewId
            );

        if (!$hasAccess) {
            throw new UnauthorizedAction();
        }

        //--------------------------------
        //-- Business action
        //--------------------------------
        $interview->cancel();

        //--------------------------------
        //-- Transaction
        //--------------------------------

        $this->transactionManager->execute(
            function () use (
                $userId,
                $interview,
                $interviewId
            ) {
                //--------------------------------
                //-- Persist interview
                //--------------------------------

                $this->interviewRepository->save(
                    $interview
                );

                //--------------------------------
                //-- Retrieve notification context
                //--------------------------------

                $interviewContext = $this->interviewRepository
                                         ->getInterviewContext(
                                            interviewId: $interviewId
                                          );

                $accountModel = $this->accountRepository
                                     ->getAccountLightModel(
                                        uuid: $userId
                                    );

                if (
                    $interviewContext === null ||
                    $accountModel === null
                ) {
                    throw new \DomainException(
                        'Unable to create interview cancellation notification.'
                    );
                }

                //--------------------------------
                //-- Notification data
                //--------------------------------

                $notificationData = new InterviewCancelledData(
                    jobId: $interviewContext->jobId,
                    jobTitle: $interviewContext->jobTitle,
                    scheduledAt: $interview->getStartDate(),
                    recruiterName: sprintf(
                        '%s %s',
                        $accountModel->firstName,
                        $accountModel->lastName
                    )
                );

                //--------------------------------
                //-- Create notification
                //--------------------------------

                $notification = Notification::create(
                    accountId: $userId,
                    recipientId: $interviewContext->candidateId,
                    data: $notificationData,
                    type: NotificationType::INTERVIEW_CANCELLED
                );

                $this->notificationRepository->save(
                    $notification
                );
            }
        );
        
    }
}