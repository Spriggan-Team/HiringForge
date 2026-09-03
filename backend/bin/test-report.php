#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Tests\Support\Reporting\JUnitReportParser;
use App\Tests\Support\Reporting\StackTraceAnalyzer;
use App\Tests\Support\Reporting\TerminalReporter;
use App\Tests\Support\Reporting\TestTypeResolver;

require dirname(__DIR__) . '/vendor/autoload.php';
$root = dirname(__DIR__);

$reportPath = $root . '/var/test-report.xml';

if (file_exists($reportPath)) {
    unlink($reportPath);
}

$startTime = microtime(true);

echo PHP_EOL;
echo "Running PHPUnit..." . PHP_EOL;
echo PHP_EOL;

$command = sprintf(
    'php "%s/bin/phpunit" tests --log-junit "%s"',
    $root,
    $reportPath
);

/*
|--------------------------------------------------------------------------
| Execute PHPUnit
|--------------------------------------------------------------------------
|
| We capture the output instead of printing it.
| Our custom reporter will be the final output.
|
*/

$output = [];
$exitCode = 0;

exec(
    $command . ' 2>&1',
    $output,
    $exitCode
);

$duration = microtime(true) - $startTime;

if (!file_exists($reportPath)) {
    echo implode(PHP_EOL, $output);
    echo PHP_EOL;
    echo "Unable to generate JUnit report." . PHP_EOL;

    exit($exitCode ?: 1);
}

$typeResolver = new TestTypeResolver();
$stackTraceAnalyzer = new StackTraceAnalyzer();

$parser = new JUnitReportParser(
    typeResolver: $typeResolver,
    stackTraceAnalyzer: $stackTraceAnalyzer
);

$report = $parser->parse($reportPath);
$report->setDuration($duration);

$terminalReporter = new TerminalReporter();
$terminalReporter->render($report);

exit($exitCode);