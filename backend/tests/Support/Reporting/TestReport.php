<?php


namespace App\Tests\Support\Reporting;

/**
 * DTO that help build test report
 * for displaying purpose.
 * (see /bin/php-report.php) 
 */
final class TestReport
{
    /**
     * @var TestResult[]
     */
    private array $results = [];

    private float $duration = 0.0;

    public function add(TestResult $result): void
    {
        $this->results[] = $result;
    }

    /**
     * @return TestResult[]
     */
    public function results(): array
    {
        return $this->results;
    }

    public function setDuration(float $duration): void
    {
        $this->duration = $duration;
    }

    public function duration(): float
    {
        return $this->duration;
    }

    public function total(): int
    {
        return count($this->results);
    }

    public function passed(): int
    {
        return count(
            array_filter(
                $this->results,
                fn(TestResult $result) => $result->status === 'PASS'
            )
        );
    }

    public function failed(): int
    {
        return count(
            array_filter(
                $this->results,
                fn(TestResult $result) => $result->status === 'FAIL'
            )
        );
    }

    public function errors(): int
    {
        return count(
            array_filter(
                $this->results,
                fn(TestResult $result) => $result->status === 'ERROR'
            )
        );
    }

    public function skipped(): int
    {
        return count(
            array_filter(
                $this->results,
                fn(TestResult $result) => $result->status === 'SKIPPED'
            )
        );
    }

    public function incomplete(): int
    {
        return count(
            array_filter(
                $this->results,
                fn(TestResult $result) => $result->status === 'INCOMPLETE'
            )
        );
    }

    /**
     * @return array<string, TestResult[]>
     */
    public function groupedByType(): array
    {
        $groups = [];

        foreach ($this->results as $result) {
            $groups[$result->type][] = $result;
        }

        return $groups;
    }

    public function successRate(): float
    {
        if ($this->total() === 0) {
            return 0;
        }

        return round(
            ($this->passed() / $this->total()) * 100,
            2
        );
    }
}