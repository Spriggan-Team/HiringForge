<?php

namespace App\Infrastructure\Persistence\Doctrine\ORM\EmploymentOffer;

use App\Domain\EmploymentOffer\EmploymentOfferStatus;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\ApplicationEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\Candidate\CandidateEntity;
use App\Infrastructure\Persistence\Doctrine\ORM\User\UserEntity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;



#[ORM\Entity]
#[ORM\Table(name: 'employment_offer')]
class EmploymentOfferEntity
{
    #[ORM\Id]
    #[ORM\Column]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $salary = null;

    #[ORM\Column(
        enumType: EmploymentOfferStatus::class
    )]
    private EmploymentOfferStatus $status = EmploymentOfferStatus::SENT;

    
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $rejectionReason = null;

    #[ORM\Column]
    private \DateTimeImmutable $expiredAt;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;


    //------------------------------
    //--- RELATIONS
    //------------------------------

    #[ORM\ManyToOne(targetEntity: ApplicationEntity::class)]
    #[ORM\JoinColumn(name: 'application_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ApplicationEntity $application;


    #[ORM\ManyToOne(targetEntity: CandidateEntity::class)]
    #[ORM\JoinColumn(name: 'candidate_id', referencedColumnName: 'id', nullable: false)]
    private CandidateEntity $candidate;

    //-----------------
    //-- construct
    //------------------

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public static function create(
        ApplicationEntity $application,
        CandidateEntity $candidate,
    )
    {
        $entity = new self();
        $entity->setApplication($application)
               ->setCandidate($candidate);
        return $entity; 
    }


    //---------------------
    //- GETTERS
    //---------------------
    
    public function getId()
    {
        return $this->id;
    }



    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getApplication(): ?ApplicationEntity
    {
        return $this->application;
    }

    public function getCandidate(): ?CandidateEntity
    {
        return $this->candidate;
    }

    public function getSalary()
    {
        return $this->salary;
    }
    public function getStatus()
    {
        return $this->status;
    }

    public function getExpiredAt()
    {
        return $this->expiredAt;
    }

    public function getCreatedAt(){
        return $this->createdAt;
    }



    public function getRejectionReason(){
        return $this->rejectionReason;
    }

    //---------------------
    //- SETTERS
    //---------------------
    

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }



    public function setApplication(?ApplicationEntity $application): self
    {
        $this->application = $application;
        return $this;
    }


    public function setCandidate(?CandidateEntity $candidate): self
    {
        $this->candidate = $candidate;
        return $this;
    }



    public function setSalary(float $salary)
    {
        $this->salary = $salary;
        return $this;
    }


    public function setStatus(EmploymentOfferStatus $status): self
    {
        $this->status = $status;
        return $this;
    }


    public function setExpiredAt(\DateTimeImmutable $expiredAt):self
    {
        $this->expiredAt = $expiredAt;
        return $this;
    }



    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setRejectionReason(?string $rejectionReason): self
    {
        $this->rejectionReason = $rejectionReason;
        return $this;
    }

}