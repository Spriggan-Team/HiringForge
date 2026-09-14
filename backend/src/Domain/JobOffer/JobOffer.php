<?php

namespace App\Domain\JobOffer;

use App\Domain\Shared\Skill\Skill;
use App\Domain\Shared\Language\Language;

use DateTimeImmutable;
use DomainException;


class JobOffer
{
    private string $id;
    private string $companyId;

    //-- Text content
    private string $title;
    private array  $content;

    /** @var array<int, JobOfferImage> - Images */
    private array $images = [];
    private ?int $locationId = null;

    //-- Categorization
    /** @var array */
    private  array  $categories; 
    private ?int $departmentId = null;
    private ?JobWorkMode $jobWorkMode = null;

    /** applications */
    /** @var array<int,string>  $applications : int => id - */
    private array $applications = [];

    //-- status & visibility
    private ?JobActivityStatus $activityStatus = null;
    private JobPublicationStatus $publicationStatus;
    private  JobOfferVisibilityStatus $visibilityStatus = JobOfferVisibilityStatus::PUBLIC;  //-- control pubication visibility


    //-- Constraint
        /** @var array<int, string>  */
    private array $skillsId = [];

        /**  @var array<int, RequiredLanguage> */
    private array $languages = [];
    private ?JobOfferExpertise $expertise = null;
    private ?int $contractId = null;


    //-- Salary
    private ?float $maxSalary = null;
    private ?float $minSalary = null;
    private ?string $currency = null;


    private DateTimeImmutable $createdAt; // appliedAt
    private DateTimeImmutable $updatedAt;
    private ?DateTimeImmutable $publicationDate = null;


    private function __construct(
        string $id,
        string $companyId,

        string $title,
        array $content,
        array $categories,

        array $images,

        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,

        JobOfferVisibilityStatus $visibilityStatus,
        ?JobActivityStatus $activityStatus,
        JobPublicationStatus $publicationStatus,

    ) {
        $this->id = $id;
        $this->companyId = $companyId;

        $this->title = $title;
        $this->content = $content;

        $this->categories = $categories;
        $this->images = $images;

        $this->visibilityStatus = $visibilityStatus;
        $this->activityStatus = $activityStatus;
        $this->publicationStatus = $publicationStatus;

        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }


    // Create an offer
    public static function create(
        string $id,
        string $companyId,

        string $title,
        array $content,

        array $images = [],
        array $categories = [],

        ?JobActivityStatus $activityStatus = null,
        JobPublicationStatus $publicationStatus = JobPublicationStatus::DRAFT,
        JobOfferVisibilityStatus $visibilityStatus = JobOfferVisibilityStatus::PUBLIC,

    ): self
    {
        if (trim($id) === '') {
            throw new DomainException(
                "JobOffer id cannot be empty"
            );
        }

        if (strlen(trim($title)) < 10) {
            throw new DomainException(
                "Job offer title must be at least 10 characters"
            );
        }

        if (empty($content)) {
            throw new DomainException(
                "Job offer content cannot be empty"
            );
        }

        $now = new DateTimeImmutable();

        return new self(
            id: $id,
            companyId: $companyId,

            title: $title,
            content: $content,
            categories: $categories,

            images: $images,

            createdAt: $now,
            updatedAt: $now,

            visibilityStatus: $visibilityStatus,
            activityStatus: $activityStatus,
            publicationStatus: $publicationStatus
        );
    }
    

    /**
     * Hydrate a JobOffer instance directly from individual parameters
     */
    public static function hydrate(
        string $id,
        string $companyId,
        string $title,
        array $content,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
        JobPublicationStatus $publicationStatus,
        JobOfferVisibilityStatus $visibilityStatus = JobOfferVisibilityStatus::PUBLIC,
        ?JobActivityStatus $activityStatus = null,
        array $categories = [],
        array $images = [],
        ?int $locationId = null,
        ?int $departmentId = null,
        ?JobWorkMode $jobWorkMode = null,
        array $applications = [],
        array $skillsId = [],
        array $languages = [],
        ?JobOfferExpertise $expertise = null,
        ?int $contractId = null,
        ?float $minSalary = null,
        ?float $maxSalary = null,
        ?string $currency = null,
        ?DateTimeImmutable $publicationDate = null
    ): self {
        $jobOffer = new self(
            id: $id,
            companyId: $companyId,
            title: $title,
            content: $content,
            categories: $categories,
            images: $images,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            visibilityStatus: $visibilityStatus,
            activityStatus: $activityStatus,
            publicationStatus: $publicationStatus
        );

        $jobOffer->locationId = $locationId;
        $jobOffer->departmentId = $departmentId;
        $jobOffer->jobWorkMode = $jobWorkMode;
        $jobOffer->applications = $applications;
        $jobOffer->skillsId = $skillsId;
        $jobOffer->languages = $languages;
        $jobOffer->expertise = $expertise;
        $jobOffer->contractId = $contractId;
        $jobOffer->minSalary = $minSalary;
        $jobOffer->maxSalary = $maxSalary;
        $jobOffer->currency = $currency;
        $jobOffer->publicationDate = $publicationDate;

        return $jobOffer;
    }



    // -------------------- Getters --------------------

    public function id(): string { return $this->id; }

    public function companyId(){
        return $this->companyId;
    }

    public function title(): string { return $this->title; }
    public function content(): array { return $this->content; }
    public function status():    JobPublicationStatus {return $this->publicationStatus;}
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }
    public function categories(): array { return $this->categories;  } 

    public function getActivityStatus() { return $this->activityStatus; }

    /** @return array<int, JobOfferImage> */
    public function images(): array
    {
        return $this->images;
    }

    /** @return array<int, string> */
    public function skillsId(): array
    {
        return $this->skillsId;
    }

    /** @return array<int, RequiredLanguage> */
    public function languages(): array
    {
        return $this->languages;
    }

    public function expertise(): ?JobOfferExpertise
    {
        return $this->expertise;
    }

    public function contractId(): ?int
    {
        return $this->contractId;
    }

    public function departmentId(): ?int
    {
        return $this->departmentId;
    }

    public function jobWorkMode(): ?JobWorkMode
    {
        return $this->jobWorkMode;
    }

    public function activityStatus(): ?JobActivityStatus
    {
        return $this->activityStatus;
    }

    public function publicationStatus(): JobPublicationStatus
    {
        return $this->publicationStatus;
    }

    public function visibilityStatus(): JobOfferVisibilityStatus
    {
        return $this->visibilityStatus;
    }

    public function minSalary(): ?float
    {
        return $this->minSalary;
    }

    public function maxSalary(): ?float
    {
        return $this->maxSalary;
    }

    public function currency(): ?string
    {
        return $this->currency;
    }

    
    public function locationId(){
        return $this->locationId;
    }
        
    public function publicationDate(){
        return $this->publicationDate;
    }



    // -------------------- Business behaviors --------------------


    public function setId(){
        return $this->id;
    }

    public function publish(): void
    {
        if ($this->publicationStatus == JobPublicationStatus::PUBLISHED) {
            throw new DomainException("Job offer already published");
        }

        $this->publicationStatus = JobPublicationStatus::PUBLISHED;
        $this->publicationDate = new DateTimeImmutable();
        $this->touch();
    }

    public function changeLocation(int $lacationId){
        if ($this->publicationStatus == JobPublicationStatus::PUBLISHED) {
            throw new DomainException("Published Job offer cannot change location");
        }
        $this->locationId = $lacationId;
        return $this;
    }


    public function rename(string $newTitle): void
    {
        if ($this->publicationStatus === JobPublicationStatus::PUBLISHED) {
            throw new DomainException("Published job offers cannot be renamed");
        }

        if (strlen(trim($newTitle)) < 10) {
            throw new DomainException("Job offer title must be at least 10 characters");
        }

        $this->title = $newTitle;
        $this->touch();
    }


    public function addImage(JobOfferImage $jobImage): void
    {
        if ($this->publicationStatus === JobPublicationStatus::PUBLISHED) {
            throw new DomainException("Published job offers cannot be renamed");
        }
        $jobImage->media->mustBe(sizeLimitation: 15728640  ); //15 mo

        foreach($this->images as $image){
            if($image->isMain)
                throw new DomainException(" Two file be set as 'main' for a given jobOffer ");
        }

        $this->images[] = $jobImage;
        $this->touch();
    }


    public function removeImage(JobOfferImage $JobImage): void
    {
        if ($this->publicationStatus === JobPublicationStatus::PUBLISHED) {
            throw new DomainException("Published job offers cannot be renamed");
        }
        $this->images = array_filter($this->images, fn($image) => $image->media->name === $JobImage->media->name); //Not reindexed
        $this->touch();
    }

    
    public function changeContent(array $newContent): void
    {
        if ($this->publicationStatus === JobPublicationStatus::PUBLISHED) {
            throw new DomainException("Published job offers cannot be edited");
        }

        if (empty($newContent)) {
            throw new DomainException("Job offer content cannot be empty");
        }

        $this->content = $newContent;
        $this->touch();
    }


    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }


    public function isPublished(): bool { return $this->publicationStatus === JobPublicationStatus::PUBLISHED; }
    

    public function changeDepartment(?int $departmentId): void
    {
        if ($this->isPublished()) {
            throw new DomainException("Published job offers cannot be edited");
        }

        $this->departmentId = $departmentId;
        $this->touch();
    }


    public function changeWorkMode(?JobWorkMode $jobWorkMode): void
    {
        if ($this->isPublished()) {
            throw new DomainException("Published job offers cannot be edited");
        }

        $this->jobWorkMode = $jobWorkMode;
        $this->touch();
    }



    public function changeExpertise(?JobOfferExpertise $expertise): void
    {
        if ($this->isPublished()) {
            throw new DomainException("Published job offers cannot be edited");
        }

        $this->expertise = $expertise;
        $this->touch();
    }



    public function changeContractType(?int $contractId): void
    {
        if ($this->isPublished()) {
            throw new DomainException("Published job offers cannot be edited");
        }

        $this->contractId = $contractId;
        $this->touch();
    }



    public function changeSalary(
        ?float $minSalary,
        ?float $maxSalary,
        ?string $currency
    ): void {
        if ($this->isPublished()) {
            throw new DomainException("Published job offers cannot be edited");
        }

        if (
            $minSalary !== null &&
            $maxSalary !== null &&
            $minSalary > $maxSalary
        ) {
            throw new DomainException("Minimum salary cannot exceed maximum salary");
        }

  
        $this->minSalary = $minSalary;
        $this->maxSalary = $maxSalary;
        $this->currency = $currency;

        $this->touch();
    }



    public function changeVisibilityStatus(JobOfferVisibilityStatus $visibility): void
    {
        $this->visibilityStatus = $visibility;
        $this->touch();
    }


    public function activate(): void
    {
        $this->activityStatus = JobActivityStatus::ACTIVE;
        $this->touch();
    }



    public function deactivate(): void
    {
        $this->activityStatus = JobActivityStatus::INACTIVE;
        $this->touch();
    }
    


    public function addSkill(string $skillsId): void
    {
        if ($this->isPublished()) {
            throw new DomainException("Published job offers cannot be edited");
        }

        foreach ($this->skillsId as $existingSkill) {
            if ($existingSkill === $skillsId) {
                return;
            }
        }

        $this->skillsId[] = $skillsId;
        $this->touch();
    }



    public function removeSkill(Skill $skillsId): void
    {
        if ($this->isPublished()) {
            throw new DomainException("Published job offers cannot be edited");
        }

        $this->skillsId = array_values(
            array_filter(
                $this->skillsId,
                fn (Skill $id) => $id !== $skillsId
            )
        );

        $this->touch();
    }


    /**
     * @param  array $skillsId
     */
    public function changeSkillsId(array $skillsId): static
    {
        $this->skillsId = $skillsId;
        return $this;
    }


    public function addLanguage(RequiredLanguage  $reqLanguage): void
    {
        if ($this->isPublished()) {
            throw new DomainException("Published job offers cannot be edited");
        }

        foreach($this->languages as $requiredLanguage){

            if(
                $requiredLanguage
                    ->language()
                    ->id() === $reqLanguage->language()->id()
            ){
                throw new DomainException(
                    "Language already required"
                );
            }
        }

        $this->languages[] = $reqLanguage;
        $this->touch();
    }
    


    public function removeLanguage(Language $language): void
    {
        if ($this->isPublished()) {
            throw new DomainException("Published job offers cannot be edited");
        }

        $this->languages = array_values(
            array_filter(
                $this->languages,
                fn (Language $item) => $item->id() !== $language->id()
            )
        );

        $this->touch();
    }
    

    public function changeCategories(array $categories): void
    {
        if ($this->isPublished()) {
            throw new DomainException("Published job offers cannot be edited");
        }

        $this->categories = $categories;
        $this->touch();
    }


    public function schedulePublication(?DateTimeImmutable $publicationDate = null){
        if ($this->isPublished()) {
            throw new DomainException("Published job offers cannot set a publication date as they are already published");
        }
        
        if($publicationDate && new DateTimeImmutable() < $publicationDate)
        {
            throw new DomainException("Published job offers cannot be published a past day");
        }

        $this->publicationDate = $publicationDate;
        $this->touch();
    }


    public function changePublicationStatus(JobPublicationStatus $pubStatus): void
    {
        if ($this->publicationStatus === $pubStatus) {
            return;
        }

        if (!$this->publicationStatus->canTransition($pubStatus)) {
            throw new \LogicException(sprintf(
                'It is not possible to change from status “%s” to status “%s”.',
                $this->publicationStatus->value,
                $pubStatus->value
            ));
        }

        //-- Specific domain rule
        if ($pubStatus === JobPublicationStatus::DRAFT && count($this->applications) > 0) {
            throw new \LogicException('A job posting with applications can no longer be set to draft status.');
        }

        //-- Apply new status
        $this->publicationStatus = $pubStatus;
    }
}
