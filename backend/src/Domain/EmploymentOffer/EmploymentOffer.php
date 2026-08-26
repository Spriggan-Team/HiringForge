<?php

namespace  App\Domain\EmploymentOffer;


class EmploymentOffer
{
    private ?string $id = null;
    private ?string $message = null;
    private ?float  $salary = null;
    
    private string $applicationId;
    private string $candidateId;

    private ?bool $confirm = null;
    private ?string $rejectionReason = null;

    private EmploymentOfferStatus $status = EmploymentOfferStatus::SENT;

    private \DateTimeImmutable $expiredAt;
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
               ->setCreatedAt(new \DateTimeImmutable());

        return $domain;
    }


    public static function hydrate(
        string $id,
        string $candidateId,
        string $applicationId,
        bool $confirm,
        string $rejectionReason,
        \DateTimeImmutable $expiredAt,
        \DateTimeImmutable $createdAt,
        ?string $salary,
        ?string $message,
    )
    {
        $domain = new self(
            applicationId: $applicationId,
            candidateId: $candidateId
        );

        $domain->id = $id;
        $domain->confirm =$confirm;
        $domain->rejectionReason = $rejectionReason;
        $domain->message = $message;
        $domain->salary = $salary;
        $domain->expiredAt = $expiredAt;
        $domain->createdAt = $createdAt;
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



    public function confirm()
    {
        return $this->confirm;
    }


    public function rejectionReason(){
        return $this->rejectionReason;
    }



    //------------------------------
    //--- SETTERS
    //------------------------------

    public function setId(string $id)
    {
        return $this->id;
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


    public function approve(): static
    {
        $this->confirm = true;
        return $this;
    }


    public function reject(string $rejectionReason): static
    {
        $this->rejectionReason = $rejectionReason;
        $this->confirm = false;
        return $this;
    }
}