<?php

namespace App\Domain\Candidate\Application;

use DomainException;

class Application{
    private ?string $id = null;

    private string $candidateId;

    private string $jobOffer;

    private JobApplicationStatus $status;

    private \DateTimeImmutable $appliedAt;

    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $jobOffer,
        string $candidateId,
        ?string $id = null,
    ){
        $this->id = $id;
        $this->jobOffer = $jobOffer;
        $this->candidateId = $candidateId;

        $this->status = JobApplicationStatus::APPLIED;
        $this->appliedAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    //- Statics function
    public static function create(
        string $jobOffer,
        string $candidateId,
        ?string $id = null
    ){
        return new self(
            id: $id,
            jobOffer: $jobOffer,
            candidateId: $candidateId,
        );
    }

    //-- Getters

    public function getId(){
        return $this->id;
    }

    public function getJobOffer(){
        return $this->jobOffer;
    }

    public function getCandidcates(){
        return $this->candidateId;
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

}
