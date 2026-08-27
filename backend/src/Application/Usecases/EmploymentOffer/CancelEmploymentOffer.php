<?php

namespace App\Application\Usecases\EmploymentOffer;

use App\Api\Responder\ApiResponse;

use App\Domain\Notification\Notification;
use App\Domain\Notification\EmploymentOfferNotificationData;
use App\Domain\EmploymentOffer\EmploymentOfferRepositoryInterface;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;

use App\Domain\Notification\NotificationType;
use App\Domain\Notification\NotificationRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class CancelEmploymentOffer
{
    public function __construct(
        private EmploymentOfferRepositoryInterface $employementOfferRepo,
        private ApplicationRepositoryInterface $applicationRepository,
        private NotificationRepositoryInterface $notificationRepo
    ){}


    /**
     * @throws NotFoundHttpException|\DomainException
     */
    public function execute(
        string $recruiterId,
        string $employmentOfferId
    )
    {   
        //-- Check validity
        $employment =  $this->employementOfferRepo->findEmploymentOfferForRecruiter(
            recruiterId: $recruiterId,
            employmentOfferId: $employmentOfferId
        );

        ApiResponse::$logger->error("recuiterId: " .$recruiterId . "  ; employmentOfferId_transmis: ". $employmentOfferId);
        if (!$employment) {
            throw new NotFoundHttpException("Employment offer not found");
        }

        if (!$employment->isCancellable()) {
            throw new \DomainException("This employment offer cannot be cancelled in its current state.");
        }

        $this->employementOfferRepo->delete($employment->id());


        //------------------------
        //-- Notification
        //------------------------

        //-- Silent Method
        try{
            $applicationContext = $this->applicationRepository->getApplicationContext(
                $employment->applicationId()
            );

            $notificationData = new EmploymentOfferNotificationData(
                employmentOfferId: $employment->id(),
                jobTitle: $applicationContext->jobTitle, 
                companyName: $applicationContext->companyName, 
                salary: $employment->salary(),
                expiresAt: $employment->expiredAt()?->format(\DateTimeInterface::ATOM)
            );

            $notification = Notification::create(
                accountId: $applicationContext->recruiterId,
                recipientId: $applicationContext->candidateId,
                type: NotificationType::EMPLOYMENT_OFFER_CANCELLED,
                data: $notificationData
            );

            $this->notificationRepo->save($notification);
        }
        catch(\Exception $notification){}
    }   
}
