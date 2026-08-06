<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\Offer;

use App\Domain\Offer\OfferStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;


use Ramsey\Uuid\Uuid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;



#[ORM\Entity]
#[ORM\Table(name: 'offer')]
class OfferEntity
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $salary = null;

    #[ORM\Column(
        enumType: OfferStatus::class
    )]
    private OfferStatus $status = OfferStatus::SENT;


    #[ORM\Column]
    private \DateTimeImmutable $expiredAt;

    #[ORM\Column]
    private \DateTimeImmutable $sentAt;

    //------------------------------
    //--- RELATIONS
    //------------------------------

    #[ORM\ManyToOne(targetEntity: ApplicationEntity::class)]
    #[ORM\JoinColumn(name: 'application_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?ApplicationEntity $application = null;


    #[ORM\ManyToOne(targetEntity: CandidateEntity::class)]
    #[ORM\JoinColumn(name: 'candidate_id', referencedColumnName: 'id', nullable: false)]
    private ?CandidateEntity $candidate = null;



    public function __construct()
    {
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getApplication(): ?ApplicationEntity
    {
        return $this->application;
    }

    public function setApplication(?ApplicationEntity $application): self
    {
        $this->application = $application;
        return $this;
    }

    public function getCandidate(): ?CandidateEntity
    {
        return $this->candidate;
    }

    public function setCandidate(?CandidateEntity $candidate): self
    {
        $this->candidate = $candidate;
        return $this;
    }

    public function getSalary()
    {
        return $this->salary;
    }

    public function setSalary(float $salary)
    {
        $this->salary = $salary;
        return $this->salary;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus(OfferStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getExpiredDate()
    {
        return $this->expiredAt;
    }

    public function setExpiredDate(\DateTimeImmutable $expiredAt):self
    {
        $this->expiredAt = $expiredAt;
        return $this;
    }

    public function getSentAt()
    {
        return $this->sentAt;
    }
}