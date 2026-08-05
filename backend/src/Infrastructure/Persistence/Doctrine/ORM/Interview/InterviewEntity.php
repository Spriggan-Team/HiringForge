<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Interview;

use App\Domain\Interviews\InterviewStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\JobOffer\JobOfferEntity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: 'interview')]
class InterviewEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid', unique: true)]
    private ?string $id = null;

    #[ORM\Column(nullable: false)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(nullable: false)]
    private ?int $minutes = null;

    #[ORM\Column]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $url;

    #[ORM\Column(enumType: InterviewStatus::class)]
    #[ORM\JoinColumn(nullable: false)]
    private InterviewStatus $status = InterviewStatus::SCHEDULED;

    #[ORM\ManyToOne(targetEntity: CandidateEntity::class, inversedBy: 'interviews' )]
    #[ORM\JoinColumn(nullable: false)]
    private CandidateEntity $candidate;

    #[ORM\ManyToOne(targetEntity: JobOfferEntity::class, inversedBy: "interviews")]
    private JobOfferEntity $jobOffer;


    //-------------------------
    //------- Constructing
    //-------------------

    private function __construct()
    {}

    public static function create(
        string $id,
        \DateTimeImmutable $startDate,
        int $minutes,
        string $description,
        CandidateEntity $candidate,
        JobOfferEntity $jobOffer,
        ?string $url = null,
        InterviewStatus $status = InterviewStatus::SCHEDULED,
    ): self {
        $entity = new self();

        $entity->id = $id;
        $entity->startDate = $startDate;
        $entity->minutes = $minutes;
        $entity->description = $description;
        $entity->candidate = $candidate;
        $entity->jobOffer = $jobOffer;
        $entity->status = $status;
        $entity->url = $url;

        return $entity;
    }


    public static function reconstitute(
        string $id,
        \DateTimeImmutable $startDate,
        int $minutes,
        string $description,
        ?string $url ,
        CandidateEntity $candidate,
        JobOfferEntity $jobOffer,
        InterviewStatus $status,
    ): self {
        $entity = new self();

        $entity->id = $id;
        $entity->startDate = $startDate;
        $entity->minutes = $minutes;
        $entity->description = $description;
        $entity->candidate = $candidate;
        $entity->jobOffer = $jobOffer;
        $entity->status = $status;
        $entity->url = $url;

        return $entity;
    }

    //======================
    //  GETTERS
    //======================

    public function getId(): string{
        return $this->id;
    }

    public function getDescription(){
        return $this->description;
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getDuration(): int
    {
        return $this->minutes;
    }

    public function getStatus(): InterviewStatus
    {
        return $this->status;
    }

    public function getCandidate(){
        return $this->candidate;
    }

    public function getJobOffer() : JobOfferEntity
    {
        return $this->jobOffer;
    }

    public function getURL()
    {
        return $this->url;
    }

    //======================
    //  SETTERS
    //======================

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }


    public function setdescription(string $description){
        $this->description = $description;
        return $this;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function setDuration(int $duration):static
    {
        $this->minutes = $duration;
        return $this;
    }

    public function setStatus(InterviewStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function setCandidate(CandidateEntity $candidate) : static
    {
        $this->candidate = $candidate;
        return $this;
    }

    public function setJobOffer(JobOfferEntity $jobOffer): static{
        $this->jobOffer = $jobOffer;
        return $this;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;
        return $this;
    }
}


