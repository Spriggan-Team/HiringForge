<?php

namespace App\Domain\Shared\Language;

class Language{
    public ?int $id = null;
    public string $code;

    public function __construct(
        string $code,
        ?string $id = null,   
    )
    {
        $this->id = $id;
        $this->code = $code;
    }

    public static function create(
        string $code,
        ?string $id = null,   
    ) : self {
        return new self(
            id: $id,
            code: $code
        );
    }

    public function id(){
        return $this->id;
    }

    public function getCode(){
        $this->code;
    }

    public function setCode(string $code){
        $this->code = $code;
        return $this;
    }
}