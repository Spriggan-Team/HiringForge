<?php

namespace App\Domain\Email;

use DomainException;

class VerificationCode
{
    public string $code;
    public function __construct(?string $code = null){
        if(!$code){
            $this->code = $this->generateCode();
            return;
        }
        if(!$this->isValid($code)){
          throw new DomainException("Invalid Verification code format");
          return;
        }
        $this->code = $code;
    }

    public function value(): string {
        return $this->code;
    }

    /**
     * 
     */
    private function generateCode(): string
    {
        return "code";
    }

    private function isValid() : bool
    {
        return true;
    }
}