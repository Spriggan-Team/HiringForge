<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\JobOffer;

use App\Domain\JobOffer\JobActivityStatus;
use App\Domain\JobOffer\JobOfferExpertise;
use App\Domain\JobOffer\JobOfferVisibilityStatus;
use App\Domain\JobOffer\JobPublicationStatus;
use App\Domain\JobOffer\JobWorkMode;


use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Department\DepartmentEntity;
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
    private string $id;

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

    
    #[ORM\OneToMany(
        mappedBy: "jobOffer",
        targetEntity: ContractTypeEntity::class,
        cascade: ["persist", "remove"],
        orphanRemoval: true
    )]
    #[ORM\JoinColumn(nullable: true)]
    private ?ContractTypeEntity $contractType = null;

    #[ORM\ManyToOne(
        targetEntity: DepartmentEntity::class,
        inversedBy: "jobOffers"
    )]
    #[ORM\JoinColumn(nullable: true)]
    private ?DepartmentEntity $department = null;
    

    #[ORM\OneToMany(
        mappedBy: "jobOffer",
        targetEntity: JobOfferLanguageEntity::class,
        cascade: ["persist", "remove"],
        orphanRemoval: true
    )]
    #[ORM\JoinColumn(nullable: true)]
    private Collection $languages;


    #[ORM\Column(nullable: false, enumType: JobPublicationStatus::class )]
    private JobPublicationStatus  $publicationStatus = JobPublicationStatus::DRAFT; //-- publication state


    #[ORM\Column(nullable: true, enumType: JobActivityStatus::class)]
    private ?JobActivityStatus $activityStatus = null;

    #[ORM\Column(nullable: false)]
    private \DateTimeImmutable $createdAt;


    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;


    #[ORM\OneToMany(
        mappedBy: 'jobOffer',
        orphanRemoval: true,
        cascade: ['persist'],
        targetEntity: JobCategoryEntity::class,
    )]
    private Collection $categories;


    #[ORM\OneToMany(
        mappedBy: 'skill',
        orphanRemoval: true,
        cascade: ['persist'],
        targetEntity: SkillEntity::class,
    )]
    #[ORM\JoinColumn(nullable: true)]
    private ?Collection $requireSkills = null;


    #[ORM\ManyToOne(inversedBy: "jobOffers", targetEntity: UserEntity::class )]
    private UserEntity $user;


    #[ORM\OneToMany(
        mappedBy: 'jobOffer',
        targetEntity: ApplicationEntity::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    #[ORM\JoinColumn(nullable: false)]
    private ?Collection $applications = null;
    
    #[ORM\OneToMany(
        mappedBy: "jobOffer",
        targetEntity: InterviewEntity::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    #[ORM\JoinColumn(nullable: false)]
    private ?Collection $interviews = null;
    
    
    #[OneToMany(
        mappedBy: "jobOffer",
        targetEntity: JobOfferImageEntity::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    #[ORM\JoinColumn(nullable: false)]
    private ?Collection $images = null;


    #[ORM\OneToMany(
        mappedBy: 'jobOffer',
        targetEntity: JobOfferViewEntity::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    private Collection $views;


    public function __construct()
    {
        $this->interviews  = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->applications =  new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->views = new ArrayCollection();
        $this->requireSkills = new ArrayCollection();
    }

    public static function reconstitue(
        string $id,
        string $title,
        array  $content,
        UserEntity $user,
        JobPublicationStatus $status,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ): JobOfferEntity
    {
        $entity = new self();
        $entity->setId($id);
        $entity->setTitle($title);
        $entity->setContent($content);
        $entity->setCreatedAt($createdAt);
        $entity->setUpdatedAt($updatedAt);
        $entity->setPublicationStatus($status);
        $entity->setUser($user);
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
    public function getJobCategories(): Collection { return $this->categories; }
    public function getInterviews() : Collection { return $this->interviews; }
    public function getImages() : Collection { return $this->images; }

    public function getViewers(): ?Collection{
        return $this->views;
    }

    /**
     * @return Collection<int, JobOfferLanguageEntity>
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
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
        $this->currency = $currency;
        return $this;
    }

        //-- Contract types
    public function setContractType(?string $contractType): static
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

}
