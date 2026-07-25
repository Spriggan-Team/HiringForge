<?php


namespace App\Api\Responder;

use App\Domain\ApplicationErrorCode;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse
{
    public static ?LoggerInterface $logger = null;

    public function __construct(
        private array $data,
        private int $statusCode = 200
    ) {}

    public static function init(LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    public static function success(mixed $data, string $message = "Everything went successfully", int $statusCode = 200): self
    {
        return new self([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data
        ], $statusCode);
    }

    public static function notice(string $message, int $statusCode = 200, ?ApplicationErrorCode $code = null,): self
    {
        return new self([
            'message' => $message,
            'code' => $code
        ], $statusCode);
    }

    /**
     * The trowable is only used to  display message in the terminal 
     */
    public static function error(
        string $message = '', array $data = [], 
        ?ApplicationErrorCode $code = null, ?\Throwable $throwable = null, int $statusCode = 400
    ): self
    {
        if ($throwable && self::$logger) {
            self::$logger->error("Caught Exception: ". $throwable->getMessage(), ['exception' => $throwable]);
        }

        return new self([
            "code" => $code,
            'status'  => 'error',
            'message' => $message,
            'data'    => $data
        ], $statusCode);
    }

    public function toJsonResponse(): JsonResponse
    {
        return new JsonResponse($this->data, $this->statusCode);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}