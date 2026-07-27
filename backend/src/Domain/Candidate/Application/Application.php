<?php

namespace App\Domain\Candidate\Application;

class Application{
    private ?string $id = null;

    private string $candidateId;

    private string $jobOffer;

    private \DateTimeImmutable $appliedAt;

    public function __construct(
        string $jobOffer,
        string $candidateId,
        \DateTimeImmutable $appliedAt,

        ?string $id = null,
    ){
        $this->id = $id;
        $this->jobOffer = $jobOffer;
        $this->candidateId = $candidateId;

        $this->appliedAt = $appliedAt;
    }

    //- Statics function
    public static function create(
        string $jobOffer,
        string $candidateId,
        \DateTimeImmutable $appliedAt,

        ?string $id = null
    ){

        return new self(
            id: $id,
            jobOffer: $jobOffer,
            candidateId: $candidateId,
            appliedAt: $appliedAt
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
}
