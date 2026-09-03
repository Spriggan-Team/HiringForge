<?php

declare(strict_types=1);

namespace App\Tests\Support\Reporting;

final class TerminalReporter
{
    private const RESET = "\033[0m";
    private const GREEN = "\033[32m";
    private const RED = "\033[31m";
    private const YELLOW = "\033[33m";
    private const CYAN = "\033[36m";
    private const GRAY = "\033[90m";
    private const BOLD = "\033[1m";

    public function render(TestReport $report): void
    {
        $this->header();

        foreach ($report->groupedByType() as $type => $results) {
            $this->section($type);

            $groupedByClass = $this->groupByClass($results);

            foreach ($groupedByClass as $class => $classResults) {
                $this->classGroup($class, $classResults);
            }

            $this->typeSummary($results);
        }

        $this->summary($report);
    }

    private function header(): void
    {
        echo PHP_EOL;
        echo "╔══════════════════════════════════════════════════════════════════════════════╗" . PHP_EOL;
        echo "║                   🧪 HIRINGFORGE TEST REPORT                                 ║" . PHP_EOL;
        echo "╚══════════════════════════════════════════════════════════════════════════════╝" . PHP_EOL;
    }

    private function section(string $title): void
    {
        echo PHP_EOL;
        echo self::BOLD . " {$title}" . self::RESET . PHP_EOL;
        echo " ─────────────────────────────────────────────────────────────────────────────" . PHP_EOL;
    }

    /**
     * @param TestResult[] $results
     *
     * @return array<string, TestResult[]>
     */
    private function groupByClass(array $results): array
    {
        $grouped = [];

        foreach ($results as $result) {
            $grouped[$result->class][] = $result;
        }

        return $grouped;
    }

    /**
     * @param TestResult[] $results
     */
    private function classGroup(
        string $class,
        array $results
    ): void {
        echo PHP_EOL;

        echo self::CYAN
            . self::BOLD
            . " " . $this->shortClassName($class)
            . self::RESET
            . PHP_EOL;

        $total = count($results);

        foreach ($results as $index => $result) {
            $isLast = $index === $total - 1;

            $this->test(
                result: $result,
                branch: $isLast ? '└──' : '├──',
                hasNextSibling: !$isLast
            );
        }
    }

    private function shortClassName(string $class): string
    {
        $parts = explode('\\', $class);

        return (string) end($parts);
    }

    private function test(
        TestResult $result,
        string $branch,
        bool $hasNextSibling
    ): void {
        [$icon, $color] = match ($result->status) {
            'PASS' => ['✓', self::GREEN],
            'FAIL' => ['✗', self::RED],
            'ERROR' => ['!', self::RED],
            'SKIPPED' => ['-', self::YELLOW],
            'INCOMPLETE' => ['?', self::YELLOW],
            default => ['?', self::GRAY],
        };

        echo sprintf(
            " %s %s%s %-10s%s %s",
            $branch,
            $color,
            $icon,
            $result->status,
            self::RESET,
            $this->humanizeTestName($result->name)
        ) . PHP_EOL;

        if ($result->status !== 'PASS') {
            $this->testDetails(
                result: $result,
                hasNextSibling: $hasNextSibling
            );
        }
    }

    private function testDetails(
        TestResult $result,
        bool $hasNextSibling
    ): void {
        $prefix = $hasNextSibling
            ? " │"
            : "  ";

        if ($result->sourceFile) {
            echo $prefix . "   📍 "
                . $result->sourceFile
                . ':'
                . ($result->sourceLine ?? '?')
                . PHP_EOL;
        }

        if ($result->message) {
            echo $prefix . "   💬 "
                . $this->truncate(
                    $this->cleanMessage($result->message),
                    140
                )
                . PHP_EOL;
        }
    }

    /**
     * Résumé d'un groupe de tests.
     *
     * @param TestResult[] $results
     */
    private function typeSummary(array $results): void
    {
        $total = count($results);

        $passed = $this->countByStatus($results, 'PASS');
        $failed = $this->countByStatus($results, 'FAIL');
        $errors = $this->countByStatus($results, 'ERROR');
        $skipped = $this->countByStatus($results, 'SKIPPED');
        $incomplete = $this->countByStatus($results, 'INCOMPLETE');

        $successRate = $total > 0
            ? ($passed / $total) * 100
            : 0;

        echo PHP_EOL;
        echo self::GRAY
            . " ── Group Summary ─────────────────────────────────────────────────────────"
            . self::RESET
            . PHP_EOL;

        echo "    Total: {$total}";

        echo self::GREEN
            . "  ✓ {$passed} passed"
            . self::RESET;

        echo self::RED
            . "  ✗ {$failed} failed"
            . self::RESET;

        echo self::RED
            . "  ! {$errors} errors"
            . self::RESET;

        echo self::YELLOW
            . "  - {$skipped} skipped"
            . self::RESET;

        echo self::YELLOW
            . "  ? {$incomplete} incomplete"
            . self::RESET;

        echo PHP_EOL;

        echo sprintf(
            "    Success rate: %.2f%%",
            $successRate
        ) . PHP_EOL;
    }

    /**
     * @param TestResult[] $results
     */
    private function countByStatus(
        array $results,
        string $status
    ): int {
        return count(
            array_filter(
                $results,
                static fn (TestResult $result): bool =>
                    $result->status === $status
            )
        );
    }

    private function summary(TestReport $report): void
    {
        echo PHP_EOL;
        echo self::BOLD . " SUMMARY" . self::RESET . PHP_EOL;
        echo " ─────────────────────────────────────────────────────────────────────────────" . PHP_EOL;
        echo PHP_EOL;

        printf(" Total Tests       %d\n", $report->total());

        echo self::GREEN
            . sprintf(" ├── ✓ Passed      %d\n", $report->passed())
            . self::RESET;

        echo self::RED
            . sprintf(" ├── ✗ Failed      %d\n", $report->failed())
            . self::RESET;

        echo self::RED
            . sprintf(" ├── ! Errors      %d\n", $report->errors())
            . self::RESET;

        echo self::YELLOW
            . sprintf(" ├── - Skipped     %d\n", $report->skipped())
            . self::RESET;

        echo self::YELLOW
            . sprintf(" └── ? Incomplete  %d\n", $report->incomplete())
            . self::RESET;

        echo PHP_EOL;

        printf(
            " Success Rate: %.2f%%\n",
            $report->successRate()
        );

        printf(
            " Duration: %.3f seconds\n",
            $report->duration()
        );

        echo PHP_EOL;
        echo " ══════════════════════════════════════════════════════════════════════════════" . PHP_EOL;
        echo PHP_EOL;

        $success = $report->failed() === 0
            && $report->errors() === 0;

        if ($success) {
            echo self::GREEN
                . self::BOLD
                . " RESULT: SUCCESS ✓"
                . self::RESET
                . PHP_EOL;
        } else {
            echo self::RED
                . self::BOLD
                . " RESULT: FAILED ✗"
                . self::RESET
                . PHP_EOL;
        }

        echo PHP_EOL;
    }

    private function humanizeTestName(string $name): string
    {
        $name = preg_replace(
            '/([a-z])([A-Z])/',
            '$1 $2',
            $name
        );

        return str_replace('_', ' ', $name ?? '');
    }

    private function cleanMessage(string $message): string
    {
        $message = preg_replace(
            '/\s+/',
            ' ',
            $message
        );

        return trim($message ?? '');
    }

    private function truncate(
        string $message,
        int $length
    ): string {
        if (mb_strlen($message) <= $length) {
            return $message;
        }

        return mb_substr(
            $message,
            0,
            $length - 3
        ) . '...';
    }
}
