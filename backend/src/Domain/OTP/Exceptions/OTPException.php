<?php

namespace App\Domain\OTP\Exceptions;

class OTPException extends \Exception {
    public bool $expired = false;
    public bool $isInvalid = false;

    public function __construct(
        bool $expired = false,
        bool $isInvalid = false,
        
        //-- extended
        ?string $code = null,
        ?string $message = null,
        ?\Throwable $previous = null
    ){
        parent::__construct(code: $code, message: $message, previous: $previous);
        $this->expired = $expired;
        $this->isInvalid = $isInvalid;
    }
}