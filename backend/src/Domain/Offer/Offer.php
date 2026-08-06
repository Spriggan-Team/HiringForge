<?php

namespace  App\Domain\Offer;

class Offer{
    private ?string $id = null;
    private ?string $title = null;
    private ?string $message = null;
    private ?float $salary = null;
    
    private string $applicationId;
    private string $candidateId;

    private OfferStatus $status = OfferStatus::SENT;

    private \DateTimeImmutable $sentAt;
    private \DateTimeImmutable $expiredAt;


    public function __construct(
        string $candidateId,
        string $applicationId
    ) {
        $this->candidateId = $candidateId;
        $this->applicationId = $applicationId;
        $this->sentAt = new \DateTimeImmutable();
    }


    public static function create(
        string $candidateId,
        string $applicationId,
        \DateTimeImmutable $expiredAt,
        ?string $title = null,
        ?float $salary = null,
        ?string $message = null
    ): self {
        $domain = new self(
            candidateId: $candidateId,
            applicationId: $applicationId
        );
        
        $domain->setTitle($title)
               ->setSalary($salary)
               ->setMessage($message)
               ->setExpiredAt($expiredAt);

        return $domain;
    }


    public static function hydrate(
        string $id,
        string $candidateId,
        string $applicationId,
        \DateTimeImmutable $expiredAt,
        ?float $title,
        ?string $salary,
        ?string $message,
    )
    {
        $domain = new self(
            applicationId: $applicationId,
            candidateId: $candidateId
        );

        $domain->id = $id;
        $domain->title = $title;
        $domain->message = $message;
        $domain->salary = $salary;
        $domain->expiredAt = $expiredAt;
    }

    
    //----------------------
    //--- Getters
    //----------------------

    public function id(){
        return $this->id;
    }

    public function title()
    {
        return $this->title;
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

    public function sentAt()
    {
        return $this->sentAt;
    }

    public function expiredAt()
    {
        return $this->expiredAt;
    }

    //------------------------------
    //--- SETTERS
    //------------------------------

    public function setId(string $id)
    {
        return $this->id;
    }

    public function setTitle(?string $title)
    {
        $this->title = $title;
        return $this;
    }

    public function changeStatus(OfferStatus $status)
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

}