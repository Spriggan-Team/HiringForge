<?php


namespace App\Tests\Support\Reporting;



/**
 * Resolve the type of what is beeing
 * tested in the test reporter (bin/test-report.php)
 */
final class TestTypeResolver
{
    public function resolve(string $class): string
    {
        return match (true) {
            str_contains($class, '\\Unit\\') => 'UNIT TESTS',
            str_contains($class, '\\Integration\\') => 'INTEGRATION TESTS',
            str_contains($class, '\\Functional\\') => 'FUNCTIONAL TESTS',
            default => 'OTHER TESTS',
        };
    }
}