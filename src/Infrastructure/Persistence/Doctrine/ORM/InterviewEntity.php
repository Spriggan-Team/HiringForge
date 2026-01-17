<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM;

use Doctrine\ORM\Mapping as ORM;
use App\Domain\Interviews\InterviewStatus;



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

    #[ORM\Column(enumType: InterviewStatus::class)]
    #[ORM\JoinColumn(nullable: false)]
    private InterviewStatus $status = InterviewStatus::SCHEDULED;

    #[ORM\ManyToOne(targetEntity: CandidateEntity::class, inversedBy: 'interviews' )]
    #[ORM\JoinColumn(nullable: false)]
    private CandidateEntity $candidate;

    #[ORM\ManyToOne(targetEntity: JobOfferEntity::class, inversedBy: "interviews")]
    private JobOfferEntity $jobOffer;

    //======================
    //  GETTERS
    //======================

    public function getId(): string{
        return $this->id;
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

    //======================
    //  SETTERS
    //======================

    public function setId(string $id): static
    {
        $this->id = $id;
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
}


