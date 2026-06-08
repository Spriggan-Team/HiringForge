<?php

namespace App\Domain\Exception;


class ExceptionWithPayload extends \Exception
{
    public function __construct(
        string $message = "",
        private readonly array $payload = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}