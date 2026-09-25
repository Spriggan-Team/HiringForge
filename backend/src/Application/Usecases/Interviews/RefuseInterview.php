<?php

namespace App\Application\Usecases\Interviews;

use App\Domain\Notification\Notification;

use App\Domain\Interviews\InterviewsRepositoryInterface;
use App\Domain\Notification\Interviews\InterviewDecisionNotificationData;
use App\Domain\Notification\NotificationRepositoryInterface;
use App\Domain\Notification\NotificationType;

class RefuseInterview
{
    public function __construct(
        private InterviewsRepositoryInterface $interviewsRepository,
        private NotificationRepositoryInterface $notificationRepository
    ){}


    /**
     * @param string $candidateId candidate's id
     * @param string $interviewId interview's id
     * @param array{
     *      rejectionReason?: string
     * } $data  - necessary data for refusing an interview  
     */
    public function execute(
        string $candidateId,
        string $interviewId,
        array $data
    ){
        if(!$this->interviewsRepository->isCandidateAssociatedWithInterview(candidateId: $candidateId, interviewId: $interviewId)){
            throw new \DomainException("This candidate is not related to the current interview");
        };

        $interview = $this->interviewsRepository->findById($interviewId);
        $interview->reject($data['rejectionReason']);
        
        $this->interviewsRepository->save($interview);
        
        try{
            $interviewContext = $this->interviewsRepository->getInterviewContext($interviewId);

            $notification =  Notification::create(
                accountId: $candidateId,
                recipientId: $interviewContext->recruiterId,
                type: NotificationType::INTERVIEW_REFUSED,
                data: new InterviewDecisionNotificationData(
                    jobId: $interviewContext->jobId,
                    jobTitle: $interviewContext->jobTitle,
                    interviewId: $interviewId,
                    candidateApproval: false,
                    scheduledAt: $interview->getStartDate(),
                    locationOrLink: $interview->getUrl()
                )
            );

            $this->notificationRepository->save($notification);
        }
        catch(\Exception){}
    }
}