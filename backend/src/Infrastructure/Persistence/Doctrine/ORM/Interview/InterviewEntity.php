<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Interview;

use App\Domain\Interviews\InterviewStatus;
use App\Domain\Interviews\InterviewType;
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

    #[ORM\Column(length: 155, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(nullable: false)]
    private ?int $minutes = null;

    #[ORM\Column]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $url;

    #[ORM\Column(nullable: true, type: Types::BOOLEAN)]
    private ?bool $confirm = null;

    #[ORM\Column(enumType: InterviewStatus::class)]
    #[ORM\JoinColumn(nullable: false)]
    private InterviewStatus $status = InterviewStatus::SCHEDULED;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $candidateApproval = false;

    #[ORM\Column(length: 522, nullable: true)]
    private ?string $rejectionReason = null;

    #[ORM\Column(enumType: InterviewType::class , nullable: true)]
    private ?InterviewType $type = null;

    //----------------------------
    //-- Relations
    //----------------------------

    #[ORM\ManyToOne(targetEntity: CandidateEntity::class, inversedBy: 'interviews' )]
    #[ORM\JoinColumn(nullable: false)]
    private CandidateEntity $candidate;

    #[ORM\ManyToOne(targetEntity: JobOfferEntity::class, inversedBy: "interviews")]
    private JobOfferEntity $jobOffer;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    //-------------------------
    //------- Constructing
    //-------------------

    private function __construct()
    {
        $this->createdAt =  new \DateTimeImmutable();
    }

    public static function create(
        string $id,
        \DateTimeImmutable $startDate,
        int $minutes,
        string $description,
        CandidateEntity $candidate,
        JobOfferEntity $jobOffer,
        ?string $title = null,
        ?string $url = null,
        InterviewStatus $status = InterviewStatus::SCHEDULED,
        ?InterviewType $type = null
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
        $entity->title = $title;
        $entity->type = $type;

        return $entity;
    }


    public static function reconstitute(
        string $id,
        \DateTimeImmutable $startDate,
        int $minutes,
        string $description,
        ?string $url ,
        ?string $title,
        CandidateEntity $candidate,
        JobOfferEntity $jobOffer,
        InterviewStatus $status,
        bool $candidateApproval,
        ?InterviewStatus $type
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
        $entity->candidateApproval = $candidateApproval;
        $entity->title = $title;
        $entity->type = $type;

        return $entity;
    }

    //======================
    //  GETTERS
    //======================

    public function getId(): string{
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
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

    public function getCandidateApproval()
    {
        return $this->candidateApproval;
    }

    public function getConfirm()
    {
        return $this->confirm;
    }

    public function getRejectionReason(){
        return $this->rejectionReason;
    }


    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getType()
    {
        return $this->type;
    }

    //======================
    //  SETTERS
    //======================

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function setTitle(?string $title){
        $this->title = $title;
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

    public function setType(?InterviewType $type): static
    {
        $this->type = $type;
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

    public function  setCandidateApproval(string $candidateApproval) : self {
        $this->candidateApproval = $candidateApproval;
        return $this;   
    }

    public function setRejectionReason(?string $reason){
        $this->rejectionReason = $reason;
        return $this;
    }

    public function  setConfirm(?bool $confirm) : static {
        $this->confirm = $confirm;
        return $this;
    }
}


