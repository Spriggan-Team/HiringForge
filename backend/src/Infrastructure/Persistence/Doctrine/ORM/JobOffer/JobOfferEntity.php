<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer;

use App\Domain\JobOffer\JobActivityStatus;
use App\Domain\JobOffer\JobOfferExpertise;
use App\Domain\JobOffer\JobOfferVisibilityStatus;
use App\Domain\JobOffer\JobPublicationStatus;
use App\Domain\JobOffer\JobWorkMode;


use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Company\CompanyEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Department\DepartmentEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Address\AddressEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Contract\ContractTypeEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Global\Skill\SkillEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Interview\InterviewEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;


use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;



#[ORM\Entity]
#[ORM\Table(name: "job_offer")]
class JobOfferEntity 
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid', unique: true)]
    private string $id ;

    #[ORM\Column(length: 255, nullable: false)]
    private string $title;

    #[ORM\Column(type: 'json', nullable: false)]
    private array $content = [];

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $maxSalary = null;

    #[ORM\Column(type: "float", nullable: true)]
    private ?float $minSalary = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $currency = null;


    #[ORM\Column(enumType: JobWorkMode::class, nullable: true)]
    private ?JobWorkMode $jobWorkMode = null;

    #[ORM\Column(enumType: JobOfferExpertise::class, nullable: true)]
    private ?JobOfferExpertise $expertise = null;


    #[ORM\Column(
        enumType: JobOfferVisibilityStatus::class
    )]
    private JobOfferVisibilityStatus $visibilityStatus = JobOfferVisibilityStatus::PUBLIC; //-- control visibility



    #[ORM\Column(nullable: false, enumType: JobPublicationStatus::class )]
    private JobPublicationStatus  $publicationStatus = JobPublicationStatus::DRAFT; //-- publication state


    #[ORM\Column(nullable: true, enumType: JobActivityStatus::class)]
    private ?JobActivityStatus $activityStatus = JobActivityStatus::INACTIVE;


    #[ORM\Column(nullable: false)]
    private \DateTimeImmutable $createdAt;


    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publicationDate = null;

    //-----------------------------
    //----- RELATIONS
    //----------------------------------

    #[ORM\ManyToOne(targetEntity: CompanyEntity::class)]
    private CompanyEntity $company;

    #[ORM\ManyToOne(targetEntity: ContractTypeEntity::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?ContractTypeEntity $contractType = null;


    #[ORM\ManyToOne(
        targetEntity: DepartmentEntity::class,
        inversedBy: "jobOffers"
    )]
    private ?DepartmentEntity $department = null;


    #[ORM\OneToMany(
        targetEntity: JobOfferSkillsEntity::class,
        mappedBy: "jobOffer",
        orphanRemoval: true,
        cascade: ['persist'],
    )]
    /** @var array<int, JobOfferSkillsEntity> */
    private Collection $skills;


    #[ORM\ManyToOne(
        targetEntity: AddressEntity::class,
        cascade: ['persist'],
    )]
    private AddressEntity $address;


    #[ORM\OneToMany(
        mappedBy: "jobOffer",
        targetEntity: JobOfferLanguageEntity::class,
        cascade: ["persist", "remove"],
        orphanRemoval: true
    )]
    private Collection $languages;


    #[ORM\OneToMany(
        mappedBy: 'jobOffer',
        orphanRemoval: true,
        cascade: ['persist'],
        targetEntity: JobCategoryEntity::class,
    )]
    private Collection $categories;



    #[ORM\ManyToOne(inversedBy: "jobOffers", targetEntity: UserEntity::class )]
    private UserEntity $user;


    #[ORM\OneToMany(
        mappedBy: 'jobOffer',
        targetEntity: ApplicationEntity::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $applications;
    

    #[ORM\OneToMany(
        mappedBy: "jobOffer",
        targetEntity: InterviewEntity::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $interviews;
    
    
    #[OneToMany(
        mappedBy: "jobOffer",
        targetEntity: JobOfferImageEntity::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $images;


    #[ORM\OneToMany(
        mappedBy: 'jobOffer',
        targetEntity: JobOfferViewEntity::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    private Collection $views;


    //-----------------------------
    //----- Constructing/Building
    //------------------------------

    public function __construct()
    {
        $this->categories   = new ArrayCollection();
        $this->skills       = new ArrayCollection();
        $this->languages    = new ArrayCollection();

        $this->applications = new ArrayCollection();
        $this->interviews   = new ArrayCollection();
        $this->images       = new ArrayCollection();
        $this->views        = new ArrayCollection();

        $this->activityStatus = JobActivityStatus::INACTIVE;

        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }


    public static function create(
        string $id,
        string $title,
        array $content,
        UserEntity $user,
        CompanyEntity $company,
        ?float $minSalary=null,
        ?string $currency = null,
        ?float $maxSalary = null,
        ?JobPublicationStatus $publicationStatus = null,
        JobOfferVisibilityStatus $visibilityStatus = JobOfferVisibilityStatus::PUBLIC,
    ): self {
        $entity = new self();
        $entity->setId($id);
        $entity->title = $title;
        $entity->content = $content;
        $entity->user = $user;
        $entity->visibilityStatus = $visibilityStatus;
        $entity->company= $company;

        if($publicationStatus === JobPublicationStatus::PUBLISHED){
            $entity->publicationDate = new \DateTimeImmutable();
        }

        $entity->setCurrency($currency);
        $entity->setMaxSalary($maxSalary);
        $entity->setMinSalary($minSalary);

        return $entity;
    }


    public static function reconstitute(
        string $id,
        string $title,
        array $content,

        UserEntity $user,

        ?float $minSalary,
        ?float $maxSalary,
        ?string $currency,
        CompanyEntity $company,

        ?JobWorkMode $jobWorkMode,
        ?JobOfferExpertise $expertise,

        ?DepartmentEntity $department,
        ?ContractTypeEntity $contractType,

        JobPublicationStatus $publicationStatus,
        JobOfferVisibilityStatus $visibilityStatus,
        ?JobActivityStatus $activityStatus,

        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ): self {
        $entity = new self();

        $entity->id = $id;
        $entity->title = $title;
        $entity->content = $content;

        $entity->user = $user;
        $entity->company = $company;

        $entity->minSalary = $minSalary;
        $entity->maxSalary = $maxSalary;
        $entity->currency = $currency;

        $entity->jobWorkMode = $jobWorkMode;
        $entity->expertise = $expertise;

        $entity->department = $department;
        $entity->contractType = $contractType;

        $entity->publicationStatus = $publicationStatus;
        $entity->visibilityStatus = $visibilityStatus;
        $entity->activityStatus = $activityStatus;

        $entity->createdAt = $createdAt;
        $entity->updatedAt = $updatedAt;

        return $entity;
    }


    /* =======================
     * GETTERS
     * ======================= */


    public function getId(): string { return $this->id; }
    public function getTitle(): string { return $this->title; }

    public function getContent(): array { return $this->content; }
    public function getPublicationStatus(): JobPublicationStatus { return $this->publicationStatus; }
    public function getActivityStatus(): JobActivityStatus { return $this->activityStatus; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }


    public function getUser(): UserEntity { return $this->user; }
    public function getApplications(): Collection { return $this->applications; }

    /** @return Collection<int,JobCategoryEntity>  */
    public function getJobCategories(): Collection { return $this->categories; }
    
    public function getInterviews() : Collection { return $this->interviews; }

    /** @return Collection<int, JobOfferImageEntity>  */
    public function getImages() : Collection { return $this->images; }

    public function getViewers(): ?Collection{
        return $this->views;
    }

    /** @return  AddressEntity  */
    public function getAddress(){
        return $this->address;
    }

    /**
     * @return Collection<int, JobOfferLanguageEntity>
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    /** @return Collection<int, JobOfferSkillsEntity> */
    public function getSkills(){
        return $this->skills;
    }


    public function getDepartment(): ?DepartmentEntity
    {
        return $this->department;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function getMaxSalary(): ?float
    {
        return $this->maxSalary;
    }

    public function getMinSalary(): ?float
    {
        return $this->minSalary;
    }

    public function getContractType(): ?string
    {
        return $this->contractType;
    }

    public function getJobWorkMode(): ?JobWorkMode
    {
        return $this->jobWorkMode;
    }

    public function getExpertise(): ?JobOfferExpertise
    {
        return $this->expertise;
    }

    public function getVisibilityStatus(): JobOfferVisibilityStatus
    {
        return $this->visibilityStatus;
    }

    public function getPublicationDate(){
        return $this->publicationDate;
    }

    public function getCompany()
    {
        return $this->company;
    }

    /* =======================
     * SETTERS
     * ======================= */

        //-- Basics
    public function setId(string $id): static
    { 
        $this->id = $id;
        return $this;
    }

    public function setTitle(string $title): static
    { 
        $this->title = $title;
        return $this;
    }

    public function setContent(array $content): static
    { 
        $this->content = $content;
        return $this;
    }

        //-- Mode (remote, onsite ..ect)
    public function setJobWorkMode(?JobWorkMode $jobWorkMode): static
    {
        $this->jobWorkMode = $jobWorkMode;

        return $this;
    }

        //-- Salary
    public function setMaxSalary(?float $maxSalary): static
    {
        if($this->currency) $this->maxSalary = $maxSalary;
        return $this;
    }

    public function setMinSalary(?float $minSalary): static
    {
        if($this->currency) $this->minSalary = $minSalary;
        return $this;
    }

    public function setCurrency(?string $currency): static
    {
        if(!$currency){
            return $this;
        }
        $this->currency = $currency;
        return $this;
    }

        //-- Contract types
    public function setContractType(?ContractTypeEntity $contractType): static
    {
        $this->contractType = $contractType;
        return $this;
    }

        //--Expertise
    public function setExpertise(?JobOfferExpertise $expertise): static
    {
        $this->expertise = $expertise;
        return $this;
    }

        //-- Department
    public function setDepartment(?DepartmentEntity $department): static
    {
        $this->department = $department;
        return $this;
    }

        //-- Status

    public function setPublicationStatus(JobPublicationStatus $publicationStatus): static
    { 
        $this->publicationStatus = $publicationStatus;
        return $this;
    }

    public function setActivityStatus(?JobActivityStatus $activityStatus = null): static{
        $this->activityStatus = $activityStatus;
        return $this;
    }


    public function setVisibilityStatus(JobOfferVisibilityStatus $visibilityStatus): static
    {
        $this->visibilityStatus = $visibilityStatus;
        return $this;
    }

        //-- Dates

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static{
        $this->updatedAt = $updatedAt;
        return $this;
    }

        //-- User

    public function setUser(UserEntity $user): static
    {
        $this->user = $user;
        return $this;
    }

        //-- Views 

    public function addViewer(JobOfferViewEntity $viewer): self{
        $this->views->add($viewer);
        return $this;
    }
    

        //-- language

    public function addLanguage(JobOfferLanguageEntity $language): static
    {
        if (!$this->languages->contains($language)) {
            $this->languages->add($language);
            $language->setJobOffer($this);
        }

        return $this;
    }

    public function removeLanguage(JobOfferLanguageEntity $language): static
    {
        if ($this->languages->removeElement($language)) {
            if ($language->getJobOffer() === $this) {
                $language->setJobOffer(null);
            }
        }

        return $this;
    }

    //--- Skills
    public function addSkill(JobOfferSkillsEntity $skill){
        if(!$this->skills->contains($skill)){
            $this->skills->add($skill);
        }
        return $this;
    }

    //-- image
    public function addImage(JobOfferImageEntity $image){
        if(!$this->images->contains($image)){
            $this->images->add($image);
        }
        return $this;
    }

    //-- Publication date
    public function setPublicationDate(\DateTimeImmutable $publicationDate)
    {
        if(new \DateTimeImmutable() < $publicationDate){
            $this->publicationDate = $publicationDate;
            return $this;
        }
        return $this;
    }

    public function setAddress(AddressEntity $address){
        $this->address = $address;
        return $this;
    }

    public function setCompany(CompanyEntity $company)
    {
        $this->company = $company;
        return $this;
    }
}
