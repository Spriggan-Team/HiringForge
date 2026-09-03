<?php


namespace App\Tests\Support\Reporting;

/**
 * DTO that help build test ressult for custom reporting
 * (see /bin/php-report.php) 
 */
final readonly class TestResult
{
    public function __construct(
        public string $class,
        public string $name,
        public string $status,
        public string $type,
        public ?string $message = null,
        public ?string $sourceFile = null,
        public ?int $sourceLine = null,
        public float $duration = 0.0,
    ) {}

    public function fullName(): string
    {
        return $this->class . '::' . $this->name;
    }
}