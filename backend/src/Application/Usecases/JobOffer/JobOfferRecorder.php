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


//-- Repositories
use App\Domain\JobOffer\JobOfferRepositioryInterface;
use App\Domain\Shared\Skill\SkillRepositoryInterface ;
use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use App\Domain\Department\DepartmentRepositoryInterface;
use App\Domain\Shared\Contract\ContractTypeRepositoryInterface;
use App\Domain\Shared\Language\LanguageRepositoryInterface;


use DomainException;



final class JobOfferRecorder
{
    public function __construct(
        private JobOfferRepositioryInterface $repository,
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
        $categories = 
            $this->categoryRepository
                ->getExistingByIds(
                    $command->categories
                );

        /*** Create base offer */
        $offer = JobOffer::create(
            id: Uuid::uuid4()->toString(),
            title: $command->title,

            content: $command->content,
            categories: $categories,

        );

        /*** Skills */
        foreach($command->skills as $skillId)
        {
            $skill = $this->skillRepository->get($skillId);
            $offer->addSkill($skill);
        }


        /**  * Required languages */
        foreach($command->languages as $languageRequest)
        {
            $language = $this->languageRepository->get(
                $languageRequest->languageId
            );


            $offer->addRequiredLanguage(
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
            $department = $this->departmentRepository->get($command->departmentId);
            $offer->changeDepartment($department);
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
            $contract =  $this->contractTypeRepository->get($command->contractTypeId);
            $offer->changeContractType($contract);
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

        /*** Persist **/
        $this->repository->save(
            $offer,
            $accountId
        );

        return $offer->id();
    }
}