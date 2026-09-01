<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Interview;

use App\Domain\Interviews\InterviewStatus;
use App\Domain\Interviews\InterviewType;

use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'interview')]
class InterviewEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue('CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[ORM\Column(type: 'guid', unique: true)]
    private ?string $id = null;

    #[ORM\Column(nullable: false)]
    private ?\DateTimeImmutable $startDate = null;

    #[ORM\Column(length: 155, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(nullable: false)]
    private ?int $minutes = null;

    #[ORM\Column(nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $url;

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

    #[ORM\ManyToOne(targetEntity: ApplicationEntity::class, inversedBy: 'interviews' )]
    private ApplicationEntity $application;

    #[ORM\ManyToOne(targetEntity: UserEntity::class)]
    private UserEntity $user;

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
        \DateTimeImmutable $startDate,
        int $minutes,
        string $description,
        ApplicationEntity $application,
        UserEntity $user,
        ?string $id = null,
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
        $entity->application = $application;
        $entity->status = $status;
        $entity->url = $url;
        $entity->user = $user;
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
        ApplicationEntity $application,
        InterviewStatus $status,
        UserEntity $user,
        bool $candidateApproval,
        ?InterviewStatus $type
    ): self {
        $entity = new self();

        $entity->id = $id;
        $entity->startDate = $startDate;
        $entity->minutes = $minutes;
        $entity->description = $description;
        $entity->application = $application;
        $entity->status = $status;
        $entity->url = $url;
        $entity->candidateApproval = $candidateApproval;
        $entity->title = $title;
        $entity->type = $type;
        $entity->user = $user;

        return $entity;
    }

    //======================
    //  GETTERS
    //======================

    public function getId(): string{
        return $this->id;
    }

    public function getUser():UserEntity{
        return $this->user;
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

    public function getMinutes(): int
    {
        return $this->minutes;
    }

    public function getStatus(): InterviewStatus
    {
        return $this->status;
    }

    public function getApplication(){
        return $this->application;
    }



    public function getURL()
    {
        return $this->url;
    }

    public function getCandidateApproval()
    {
        return $this->candidateApproval;
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

    public function setApplication(ApplicationEntity $application) : static
    {
        $this->application = $application;
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

    public function setUser(UserEntity $user){
        $this->user = $user;
        return $this;
    }
}


