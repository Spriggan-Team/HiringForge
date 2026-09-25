<?php



namespace App\Application\Usecases\Interviews;

use App\Domain\Interviews\InterviewsRepositoryInterface;
use App\Domain\Notification\Interviews\InterviewDecisionNotificationData;
use App\Domain\Notification\Notification;

use App\Domain\Notification\NotificationRepositoryInterface;
use App\Domain\Notification\NotificationType;



class ConfirmInterview
{
    public function __construct(
        private InterviewsRepositoryInterface $interviewsRepository,
        private NotificationRepositoryInterface $notificationRepository
    ){}


    /**
     * @throws \Exception
     */
    public function execute(string $candidateId, string $interviewId){
        if(!$this->interviewsRepository->isCandidateAssociatedWithInterview(candidateId: $candidateId, interviewId: $interviewId)){
            throw new \DomainException("This candidate is not related to the current interview");
        };

        $interview = $this->interviewsRepository->findById($interviewId);
        $interview->accept();
        
        $this->interviewsRepository->save($interview);
        
        try{
            $interviewContext = $this->interviewsRepository->getInterviewContext($interviewId);

            $notification =  Notification::create(
                accountId: $candidateId,
                recipientId: $interviewContext->recruiterId,
                type: NotificationType::INTERVIEW_ACCEPTED,
                data: new InterviewDecisionNotificationData(
                    jobId: $interviewContext->jobId,
                    jobTitle: $interviewContext->jobTitle,
                    interviewId: $interviewId,
                    candidateApproval: true,
                    scheduledAt: $interview->getStartDate(),
                    locationOrLink: $interview->getUrl()
                )
            );

            $this->notificationRepository->save($notification);
        }
        catch(\Exception){}
    }
}