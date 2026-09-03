<?php

namespace App\Domain\OTP\Exceptions;



class OTPException extends \Exception {
    public bool $expired = false;
    public bool $isInvalid = false;

    public function __construct(
        bool $expired = false,
        bool $isInvalid = false,
        
        //-- extended (int au lieu de string pour $code)
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null
    ){
        parent::__construct($message, $code, $previous);
        $this->expired = $expired;
        $this->isInvalid = $isInvalid;
    }
}