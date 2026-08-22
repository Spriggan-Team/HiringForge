<?php

namespace App\Domain\Candidate;

final class CandidateResume{
    public function __construct(
        public string $fileId,
        public string $candidateId 
    ){}

    public static function create(
        string $fileId,
        string $candidateId 
    ){
        return new self(
            fileId: $fileId,
            candidateId: $candidateId
        );
    }

    public function fileId(){
        return $this->fileId;
    }

    public function candidateId() {
        return $this->candidateId;
    }
} 