<?php

namespace App\Application\Usecases\EmploymentOffer;

use App\Application\DTO\EmploymentOffer\CreateOfferRequestDto;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\EmploymentOffer\EmploymentOffer;

use App\Domain\EmploymentOffer\EmploymentOfferRepositoryInterface;
use App\Domain\Notification\EmploymentOfferNotificationData;
use App\Domain\Notification\Notification;

use App\Domain\Notification\NotificationRepositoryInterface;
use App\Domain\Notification\NotificationType;


class CreateEmploymentOffer
{
    public function __construct(
        private EmploymentOfferRepositoryInterface $employementOfferRepository,
        private NotificationRepositoryInterface $notificationRepository,
        private ApplicationRepositoryInterface $applicationRepository
    ){}


    public function execute(string $userId, CreateOfferRequestDto $command): EmploymentOffer
    {
        // Verify
        if (!$this->employementOfferRepository->canCreateEmploymentOfferForApplication(
            applicationId: $command->applicationId,
            candidateId: $command->candidateId
        )) {
            throw new \DomainException(
                "User {$userId} is unable to create job offer for application ({$command->applicationId}) and candidate ({$command->candidateId})"
            );
        }

        $employementOffer = EmploymentOffer::create(
            message: $command->message,
            salary: $command->salary,
            expiredAt: $command->expiredAt,
            candidateId: $command->candidateId,
            applicationId: $command->applicationId
        );

        $this->employementOfferRepository->save($userId, $employementOffer);

        //-----------------------
        // --- Notification
        //-----------------------

        $applicationContext = $this->applicationRepository->getApplicationContext(
            applicationId: $command->applicationId
        ); 

        //-- Slient notification update
        try{
            // notification Data
            $notificationData = new EmploymentOfferNotificationData(
                employmentOfferId: $employementOffer->id(),
                jobTitle: $applicationContext->jobTitle ?? $command->jobTitle, 
                companyName: $applicationContext->companyName, 
                salary: $command->salary,
                expiresAt: $command->expiredAt?->format(\DateTimeInterface::ATOM)
            );

            $notification = Notification::create(
                accountId: $applicationContext->recruiterId,
                recipientId: $command->candidateId,
                type: NotificationType::EMPLOYMENT_OFFER_GENERATED,
                data: $notificationData
            );

            $this->notificationRepository->save($notification);
        }
        catch(\Exception $error){}

        return $employementOffer;
    }
}

