<?php

namespace App\Domain\Shared\Language;

class Language{
    public ?int $id = null;
    public string $code;
    public ?string $label = null;

    public function __construct(
        string $code,
        ?string $id = null,  
        ?string $label = null
    )
    {
        $this->id = $id;
        $this->code = $code;
        $this->label = $label;
    }

    public static function create(
        string $code,
        ?string $id = null,   
        ?string $label = null,
    ) : self {
        return new self(
            id: $id,
            code: $code,
            label: $label
        );
    }


    //-------------
    //---- GETTERS
    //----------------


    public function id(){
        return $this->id;
    }

    public function code(){
        return $this->code;
    }

    public function label(){
        return $this->label;
    }


    //-------------
    //---- SETTERS
    //----------------

    public function setId(int $id){
        $this->id =$id;
        return $this;
    }

    public function setCode(string $code){
        $this->code = $code;
        return $this;
    }

    public function setLabel(string $label){
        $this->label = $label;
        return $this;
    }
}