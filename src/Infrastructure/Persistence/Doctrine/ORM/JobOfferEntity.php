<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM;

use App\Domain\JobOffer\JobStatus;
use Doctrine\ORM\Mapping as ORM;
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

    #[ORM\Column(nullable: false)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: false, enumType: JobStatus::class )]
    private JobStatus  $status;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToMany(
        mappedBy: 'jobOffer',
        targetEntity: JobCategoryEntity::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    private Collection $jobCategories;


    #[ORM\ManyToOne( inversedBy: "posts", targetEntity: UserEntity::class )]
    #[ORM\JoinColumn(nullable: false)]
    private UserEntity $user;

    #[ORM\OneToMany(
        mappedBy: 'jobOffer',
        targetEntity: ApplicationEntity::class,
        cascade: ['persist'],
        orphanRemoval: true
    )]
    private Collection $applications;

    #[ORM\OneToMany(mappedBy: "jobOffer", targetEntity: InterviewEntity::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $interviews;

    public function __construct()
    {
        $this->interviews  = new ArrayCollection();
        $this->jobCategories = new ArrayCollection();
        $this->applications =  new ArrayCollection();
    }

    public static function reconstitue(
        string $id,
        string $title,
        array  $content,
        UserEntity $user,
        JobStatus $status,
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
        $entity->setStatus($status);
        $entity->setUser($user);
        return $entity;
    }

    /* =======================
     * GETTERS
     * ======================= */

    public function getId(): string { return $this->id; }
    public function getTitle(): string { return $this->title; }

    public function getContent(): array { return $this->content; }
    public function getStatus(): JobStatus { return $this->status; }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }


    public function getUser(): UserEntity { return $this->user; }
    public function getApplications(): Collection { return $this->applications; }
    public function getJobCategories(): Collection { return $this->jobCategories; }
    public function getInterviews() : Collection { return $this->interviews; }

    /* =======================
     * SETTERS
     * ======================= */

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

    public function setStatus(JobStatus $status): static
    { 
        $this->status = $status;
        return $this;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static{
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function setUser(UserEntity $user): static
    {
        $this->user = $user;
        return $this;
    }
}
