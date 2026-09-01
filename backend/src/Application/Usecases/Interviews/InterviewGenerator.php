<?php


namespace App\Application\Usecases\Interviews;

use App\Domain\Interviews\InterviewsRepositoryInterface;
use App\Application\DTO\Interviews\CreateInterviewRequest;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\Interviews\Interview;
use App\Domain\Notification\InterviewScheduledData;

use App\Domain\Notification\Notification;
use App\Domain\Notification\NotificationRepositoryInterface;
use App\Domain\Notification\NotificationType;

use App\Domain\Shared\TransactionManagerInterface;
use App\Domain\User\UserRepositoryInterface;


class InterviewGenerator
{
    public function __construct(
        private InterviewsRepositoryInterface $interviewRepository,
        private ApplicationRepositoryInterface $applicationRepository,
        private NotificationRepositoryInterface $notificationRepository,
        private TransactionManagerInterface $transactionManagerInterface,
        private UserRepositoryInterface $userRepository,
    ){}

    /**
     * Create Interview
     */
    public function execute(string $userId, CreateInterviewRequest $command){
        //-- Check requirement
        $this->applicationRepository->assertRecruiterHasAccessToApplication(
            recruiterId: $userId,
            candidateId: $command->candidateId,
            applicationId: $command->applicationId
        );

        $concurrent = $this->interviewRepository->findConcurrentInterviews(
            startDate: $command->scheduledAt,
            minutes: $command->minutes
        );
    
        if($concurrent){
            throw new \App\Domain\Exception\ConcurrentInterviewsException();
        }

        $interview = Interview::create(
            minutes: $command->minutes,
            startDate: $command->scheduledAt,
            applicationId: $command->applicationId,
            userId: $userId,
            title: $command->title,
            description: $command->description,
            url: $command->url,
            type: $command->type,
        );

        $this->transactionManagerInterface->execute(function() use($interview, $command, $userId){
            $this->interviewRepository->save($interview);

            //--------------------
            //-- Notification
            //--------------------
            $userLightModel = $this->userRepository->getLightModelById($userId);
            $applicationContext = $this->applicationRepository->getApplicationContext(applicationId: $command->applicationId);

            $recruiterFullName = sprintf('%s %s', $userLightModel['firstName'], $userLightModel['lastName']);

            $notificationData = new InterviewScheduledData(
                jobId: $applicationContext->jobId,
                jobTitle: $applicationContext->jobTitle,
                scheduledAt: $command->scheduledAt,
                locationOrLink: $command->url,
                recruiterName: $recruiterFullName
            );

            $notification =  Notification::create(
                data: $notificationData,
                type: NotificationType::INTERVIEW_SCHEDULED,
                accountId: $userId,
                recipientId: $command->candidateId,
            );
            $this->notificationRepository->save($notification);
        });
    }
}