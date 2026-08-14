<?php

namespace App\Domain\Candidate\Application;

use DomainException;

class Application{
    private ?string $id = null;

    private string $candidateId;

    private string $jobOfferId;

    private string $companyId;

    private ?float $score = null;

    private JobApplicationStatus $status;

    private \DateTimeImmutable $appliedAt;

    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $jobOfferId,
        string $candidateId,
        string $companyId,
        ?string $id = null,
    ){
        $this->id = $id;
        $this->jobOfferId = $jobOfferId;
        $this->candidateId = $candidateId;

        $this->status = JobApplicationStatus::APPLIED;
        $this->appliedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    //- Statics function
    public static function create(
        string $jobOfferId,
        string $candidateId,
        string $companyId,
        ?string $id = null,
        ?float $score = null
    ){
        $domain =  new self(
            id: $id,
            companyId: $companyId,
            jobOfferId: $jobOfferId,
            candidateId: $candidateId,
        );

        $domain->setScore($score);

        return $domain;
    }

    //-- Getters

    public function getId(){
        return $this->id;
    }

    public function getJobOfferId(){
        return $this->jobOfferId;
    }

    public function getCandidateId(){
        return $this->candidateId;
    }

    public function getCompanyId(){
        return $this->companyId;
    }

    public function getScore(){
        return $this->score;
    }

    public function getAppliedAt(){
        return $this->appliedAt;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    //--------------------------
    //--------- SETTERS
    //--------------------------

    
    public function touch()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function changeStatus(JobApplicationStatus $status)
    {
        if(!$this->status->canTransitionTo($status)){
            throw new DomainException("Unauthorized application transistion");
        }
        $this->status = $status;
        $this->touch();
        return $this;
    }


    public function setScore(?float $score){
        $this->score = $score;
        return $this;
    }
}
