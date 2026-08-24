<?php

namespace App\Domain\Candidate;


final class CandidateResume
{

    //-----------------------------
    //--- Construct/Building
    //----------------------------

    public function __construct(
        private string $fileId,
        private string $candidateId,
        private ?string $id = null,
    ){}

    public static function create(
        string $fileId,
        string $candidateId,
        ?string $id = null,
    ){
        return new self(
            id: $id,
            fileId: $fileId,
            candidateId: $candidateId
        );
    }

    public static function hydrate(
        string $id,
        string $fileId,
        string $candidateId,
    ): self{
        return new self(
            id: $id,
            fileId: $fileId,
            candidateId: $candidateId
        );
    }

    //-------------------------
    //------- GETTERS
    //--------------------------

    public function id()
    {
        return $this->id;
    }

    public function fileId(){
        return $this->fileId;
    }

    public function candidateId() {
        return $this->candidateId;
    }

  
} 