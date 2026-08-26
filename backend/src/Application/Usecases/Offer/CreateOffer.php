<?php

namespace App\Application\Usecases\Offer;

use App\Application\DTO\Offer\CreateOfferRequestDto;
use App\Domain\Candidate\Application\Repositories\ApplicationRepositoryInterface;
use App\Domain\EmploymentOffer\EmploymentOffer;

use App\Domain\EmploymentOffer\EmploymentOfferRepositoryInterface;
use App\Domain\Notification\EmploymentOfferNotificationData;
use App\Domain\Notification\Notification;

use App\Domain\Notification\NotificationRepositoryInterface;
use App\Domain\Notification\NotificationType;


class CreateOffer
{
    public function __construct(
        private EmploymentOfferRepositoryInterface $employementOfferRepository,
        private NotificationRepositoryInterface $notificationRepository,
        private ApplicationRepositoryInterface $applicationRepository
    ){}

    public function execute(string $userId, CreateOfferRequestDto $command): EmploymentOffer
    {
        $expiredAt = new \DateTimeImmutable($command->expiredAt);
        
        if (!$this->employementOfferRepository->canCreateEmploymentOfferForApplication(
            recuiterId: $userId,
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
            expiredAt: $expiredAt,
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

        $notificationData = new EmploymentOfferNotificationData(
            employmentOfferId: $employementOffer->id(),
            jobTitle: $applicationIdentity['jobTitle'] ?? $command->jobTitle, 
            companyName: $applicationContext->companyName, 
            salary: $command->salary,
            expiresAt: $expiredAt->format(\DateTimeInterface::ATOM)
        );

        $notification = Notification::create(
            accountId: $command->candidateId,
            recipientId: $command->candidateId,
            type: NotificationType::EMPLOYMENT_OFFER_RECEIVED,
            data: $notificationData
        );

        $this->notificationRepository->save($notification);

        return $employementOffer;
    }
}

