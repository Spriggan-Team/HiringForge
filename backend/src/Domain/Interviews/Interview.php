<?php

namespace App\Domain\Interviews;

class Interview {
    private ?string $id = null;

    private ?\DateTimeImmutable $startDate = null;

    private ?int $minutes = null;

    private InterviewStatus $status = InterviewStatus::SCHEDULED;

    public function __construc(
        int $minutes,
        InterviewStatus $status = InterviewStatus::SCHEDULED,
        
        ?string $id = null,
        ?\DateTimeImmutable $startDate =null,
    ){
        $this->id = $id;
        $this->status = $status;
        $this->minutes = $minutes;
        $this->startDate = $startDate;
    }

    // -- Statics
    public function create(
        int $minutes,
        InterviewStatus $status = InterviewStatus::SCHEDULED,
        
        ?string $id = null,
        ?\DateTimeImmutable $startDate =null,
    ){
        return new self(
            id: $id,
            status: $status,
            minutes: $minutes,
            startDate: $startDate
        );
    }

    //-- Getters

    public function getId(){
        return $this->id;
    }


    public function getStatus()  {
        return $this->status;
    }

    public function getMinutes(){
        return $this->minutes;
    }

    public function getStartDate(){
        return $this->startDate;
    }
}