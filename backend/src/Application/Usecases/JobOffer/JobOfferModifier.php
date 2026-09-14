<?php

namespace App\Application\Usecases\JobOffer;

use App\Api\Responder\ApiResponse;
use App\Application\DTO\JobOffer\UpdateJobOfferRequest;
use App\Domain\JobOffer\JobOfferExpertise;

use App\Domain\JobOffer\JobOfferRepositoryInterface;
use App\Domain\JobOffer\JobOfferVisibilityStatus;
use App\Domain\JobOffer\JobPublicationStatus;
use App\Domain\JobOffer\JobWorkMode;


class JobOfferModifier
{
    public function __construct(private JobOfferRepositoryInterface $repository){}

 
    /**
     * Usecase: Update job offer information
     * @throws ResourceNotFoundException|\DomainException|\LogicException
     */
    public function execute(
        string $userId,
        UpdateJobOfferRequest $command
    ): void {
        //--------------------------
        //-- Check validity
        //--------------------------
        $this->repository->assertRelationWithUser(accountId: $userId, offerId: $command->id);

        //------------------------
        //-- Updating
        //-------------------------

        //  Aggregate Recovery
        $offer = $this->repository->findById($userId, $command->id);

        // Updating Textual Information
        if (!empty($command->title)) {
            $offer->rename($command->title);
        }

        if (!empty($command->content)) {
            $offer->changeContent($command->content);
        }

        // Updating Relationships & Characteristics
        if (!empty($command->location['id'])) {
            $offer->changeLocation((int) $command->location['id']);
        }

        if ($command->departmentId !== null) {
            $offer->changeDepartment($command->departmentId);
        }

        if ($command->contractTypeId !== null) {
            $offer->changeContractType((int) $command->contractTypeId);
        }

        if ($command->workMode !== null) {
            $offer->changeWorkMode(JobWorkMode::from($command->workMode));
        }

        if ($command->expertise !== null) {
            $offer->changeExpertise(JobOfferExpertise::from($command->expertise));
        }

        //  Payroll Management
        if ($command->salary !== null) {
            $offer->changeSalary(
                minSalary: $command->salary->min,
                maxSalary: $command->salary->max,
                currency: $command->salary->currency
            );
        }

        // Cat & Skills
        if (!empty($command->categories)) {
            $offer->changeCategories($command->categories);
        }

        if(!empty($command->skills)){
            $offer->changeSkillsId($command->skills ?? []);
        }

        // Statuts & Visibility
        if ($command->visibilityStatus !== null) {
            $offer->changeVisibilityStatus(JobOfferVisibilityStatus::from($command->visibilityStatus));
        }

        if ($command->publicationStatus !== null) {
            ApiResponse::$logger->error("Publication state, current : ".$offer->publicationStatus()->value . " ; new : " . $command->publicationStatus);
            $offer->changePublicationStatus(JobPublicationStatus::from($command->publicationStatus));
            ApiResponse::$logger->error("Publication state, current : ".$offer->publicationStatus()->value . " ; new : " . $command->publicationStatus);
        }

        if ($command->publicationDate !== null) {
            $offer->schedulePublication($command->publicationDate);
        }

        // 7. Persistance
        $this->repository->save(
            offer: $offer,
            userId: $userId
        );
    }
}