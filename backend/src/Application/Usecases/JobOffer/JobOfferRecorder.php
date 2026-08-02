<?php

namespace App\Application\Usecases\JobOffer;


use Ramsey\Uuid\Uuid; 
use App\Application\DTO\JobOffer\CreateJobOfferRequest;


use App\Domain\JobOffer\JobOffer;
use App\Domain\JobOffer\RequiredLanguage;
use App\Domain\Shared\Account\AccountId;
use App\Domain\Shared\LanguageLevel;
use App\Domain\JobOffer\JobWorkMode;
use App\Domain\JobOffer\JobOfferExpertise;
use App\Domain\JobOffer\JobOfferVisibilityStatus;
use App\Domain\JobOffer\JobPublicationStatus;


//-- Repositories
use App\Domain\JobOffer\JobOfferRepositoryInterface;
use App\Domain\Shared\Skill\SkillRepositoryInterface ;
use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use App\Domain\Company\CompanyRepositoryInterface;
use App\Domain\Department\DepartmentRepositoryInterface;
use App\Domain\Shared\Contract\ContractTypeRepositoryInterface;
use App\Domain\Shared\Language\LanguageRepositoryInterface;


use DomainException;



final class JobOfferRecorder
{
    public function __construct(
        private JobOfferRepositoryInterface $repository,
        private CompanyRepositoryInterface $companyRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private SkillRepositoryInterface $skillRepository,
        private LanguageRepositoryInterface $languageRepository,
        private DepartmentRepositoryInterface $departmentRepository,
        private ContractTypeRepositoryInterface $contractTypeRepository,
    ){}


    public function execute(
        string $accountId,
        CreateJobOfferRequest $command
    ): string
    {
        /** * Account owner */
        $accountId = AccountId::create($accountId);


        /*** Validate categories existence  */
        $categories = $this->categoryRepository->getExistingByIds($command->categories);

        /*** Create base offer */
        $offer = JobOffer::create(
            id: Uuid::uuid4()->toString(),
            title: $command->title,

            content: $command->content,
            categories: $categories,

        );

        /*** Skills */
        $validSkillIds = $this->skillRepository->findExistingIds(
            $command->skills
        );
        if(empty(array_diff($command->skills, $validSkillIds))){
            foreach($command->skills as $skillId)
            {
                $offer->addSkill($skillId);
            }
        }


        /**  * Required languages */
        foreach($command->languages as $languageRequest)
        {
            $language = $this->languageRepository->get(
                $languageRequest->languageId
            );

            $offer->addLanguage(
                new RequiredLanguage(
                    language: $language,
                    level: LanguageLevel::from(
                        $languageRequest->level
                    )
                )

            );
        }


        /*** Department */
        if($command->departmentId)
        {
            if($this->departmentRepository->exists($command->departmentId)){
                $offer->changeDepartment($command->departmentId);
            }
        }

        /*** Work mode */
        if($command->workMode)
        {
            $workMode = JobWorkMode::tryFrom($command->workMode );
            $offer->changeWorkMode(
                $workMode
            );
        }

        /*** Expertise */
        if($command->expertise)
        {
            $expertise =  JobOfferExpertise::tryFrom($command->expertise);
            $offer->changeExpertise(
                $expertise
            );
        }

        /*** Contract type */
        if($command->contractTypeId)
        {
            $doesContractTypeExist =  $this->contractTypeRepository->exists($command->contractTypeId);
            if($doesContractTypeExist){
                $offer->changeContractType($command->contractTypeId);
            }
        }

        
        /** * Salary      */
        if($command->salary)
        {
            $offer->changeSalary(
                minSalary: $command->salary->min,
                maxSalary: $command->salary->max,
                currency: $command->salary->currency
            );
        }

        /*** Visibility   */
        if($command->visibilityStatus)
        {
            $visibilityStatus = JobOfferVisibilityStatus::tryFrom($command->visibilityStatus);
            if($visibilityStatus)
                $offer->changeVisibilityStatus($visibilityStatus);
        }


        /*** Publication date    */
        if($command->publicationDate)
        {
            $offer->schedulePublication($command->publicationDate);
        }

        /** Location */
        if($command->location && $this->companyRepository->isAddressOwnedByUserCompany(
            addressId: $command->location["id"], userId: $accountId->value())
        ){
            $offer->changeLocation($command->location["id"]);
        }

        /**  Publication */
        if($command->publicationStatus){
            $visibilityStatus = JobPublicationStatus::tryFrom($command->publicationStatus);
            if($visibilityStatus == JobPublicationStatus::PUBLISHED){
                $offer->publish();
            }
        }

        /*** Persist **/
        $this->repository->save(
            $offer,
            $accountId
        );

        return $offer->id();
    }
}