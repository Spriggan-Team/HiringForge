<?php

namespace  App\Domain\EmploymentOffer;


class EmploymentOffer
{
    private ?string $id = null;
    private ?string $message = null;
    private ?float  $salary = null;
    
    private string $applicationId;
    private string $candidateId;

    private ?string $rejectionReason = null;

    private EmploymentOfferStatus $status = EmploymentOfferStatus::SENT;

    private \DateTimeImmutable $expiredAt;
    private \DateTimeImmutable $scheduledEndDate;
    private \DateTimeImmutable $createdAt;


    public function __construct(
        string $candidateId,
        string $applicationId,
    ) {
        $this->candidateId = $candidateId;
        $this->applicationId = $applicationId;
        $this->createdAt = new \DateTimeImmutable();
    }


    public static function create(
        string $candidateId,
        string $applicationId,
        \DateTimeImmutable $expiredAt,
        \DateTimeImmutable $scheduledEndDate,
        ?float $salary = null,
        ?string $message = null
    ): self {
        $domain = new self(
            candidateId: $candidateId,
            applicationId: $applicationId
        );
        
        $domain->setSalary($salary)
               ->setMessage($message)
               ->setExpiredAt($expiredAt)
               ->setCreatedAt(new \DateTimeImmutable())
               ->setScheduledEndDate($scheduledEndDate);
        return $domain;
    }


    public static function hydrate(
        string $id,
        EmploymentOfferStatus $status,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $expiredAt,
        \DateTimeImmutable $scheduledEndDate,
        ?string $candidateId = null,
        ?string $applicationId = null,
        ?float $salary = null,
        ?string $message = null,
        ?string $rejectionReason = null
    ): self
    {
        $domain = new self(
            applicationId: $applicationId,
            candidateId: $candidateId
        );

        $domain->id = $id;
        $domain->status = $status;
        $domain->rejectionReason = $rejectionReason;
        $domain->message = $message;
        $domain->salary = $salary;
        $domain->expiredAt = $expiredAt;
        $domain->createdAt = $createdAt;
        $domain->scheduledEndDate = $scheduledEndDate;

        return $domain;
    }

    
    //----------------------
    //--- Getters
    //----------------------

    public function id(){
        return $this->id;
    }


    public function status()
    {
        return $this->status;
    }

    public function salary()
    {
        return $this->salary;
    }

    public function message() 
    {
        return $this->message;    
    }

    public function candidateId(){
        return $this->candidateId;
    }

    public function applicationId()
    {
        return $this->applicationId;
    }



    public function expiredAt()
    {
        return $this->expiredAt;
    }


    public function createdAt(){
        return $this->createdAt;
    }



    public function rejectionReason(){
        return $this->rejectionReason;
    }

    public function scheduledEndDate()
    {
        return $this->scheduledEndDate;
    }

    //------------------------------
    //--- SETTERS
    //------------------------------

    public function setId(string $id)
    {
        $this->id = $id;
        return $this;
    }

    public function changeStatus(EmploymentOfferStatus $status)
    {
        $this->status = $status;
        return $this;
    }

    public function setSalary(?float $salary)
    {
        $this->salary = $salary;
        return $this;
    }

    public function setMessage(?string $message)
    {
        $this->message = $message;
        return $this;
    }

    public function setExpiredAt(\DateTimeImmutable $expiredAt)
    {
        $this->expiredAt = $expiredAt;
        return $this;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static{
        $this->createdAt = $createdAt;
        return $this;
    }

    
    /** Check if domain allow cancellation */
    public function isCancellable(): bool
    {
        $now = new \DateTimeImmutable();

        if ($this->expiredAt < $now) {
            return false;
        }
        
        $isValid = $this->scheduledEndDate > $now;

        return $isValid && (
            $this->status === EmploymentOfferStatus::SENT ||
            $this->status === EmploymentOfferStatus::DRAFT
        );
    }

    public function setScheduledEndDate(\DateTimeImmutable $scheduledEndDate): static
    {
        $this->scheduledEndDate = $scheduledEndDate;
        return $this;
    }
}