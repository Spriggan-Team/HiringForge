<?php

declare(strict_types=1);

namespace App\Tests\Support\Reporting;

final class JUnitReportParser
{
    public function __construct(
        private readonly TestTypeResolver $typeResolver,
        private readonly StackTraceAnalyzer $stackTraceAnalyzer,
    ) {}

    public function parse(string $file): TestReport
    {
        if (!file_exists($file)) {
            throw new \RuntimeException(
                sprintf('JUnit report not found: %s', $file)
            );
        }

        $xml = simplexml_load_file($file);

        if ($xml === false) {
            throw new \RuntimeException(
                'Unable to parse JUnit report.'
            );
        }

        $report = new TestReport();

        $testCases = $xml->xpath('//testcase') ?: [];

        foreach ($testCases as $testCase) {

            $class = (string) $testCase['class'];
            $name = (string) $testCase['name'];
            $duration = (float) $testCase['time'];

            $status = 'PASS';
            $message = null;
            $trace = '';

            if (isset($testCase->failure)) {
                $status = 'FAIL';

                $message = trim(
                    (string) $testCase->failure['message']
                );

                $trace = (string) $testCase->failure;
            }

            if (isset($testCase->error)) {
                $status = 'ERROR';

                $message = trim(
                    (string) $testCase->error['message']
                );

                $trace = (string) $testCase->error;
            }

            if (isset($testCase->skipped)) {
                $status = 'SKIPPED';

                $message = trim(
                    (string) $testCase->skipped['message']
                );
            }

            $source = [
                'file' => null,
                'line' => null,
            ];

            if ($trace !== '') {
                $source = $this
                    ->stackTraceAnalyzer
                    ->findFirstApplicationSource($trace);
            }

            $report->add(
                new TestResult(
                    class: $this->shortClassName($class),
                    name: $name,
                    status: $status,
                    type: $this->typeResolver->resolve($class),
                    message: $message,
                    sourceFile: $source['file'],
                    sourceLine: $source['line'],
                    duration: $duration,
                )
            );
        }

        return $report;
    }

    private function shortClassName(string $class): string
    {
        $parts = explode('\\', $class);

        return end($parts);
    }
}