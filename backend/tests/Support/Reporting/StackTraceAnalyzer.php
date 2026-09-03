<?php

declare(strict_types=1);

namespace App\Tests\Support\Reporting;

final class StackTraceAnalyzer
{
    /**
     * @return array{file: ?string, line: ?int}
     */
    public function findFirstApplicationSource(string $trace): array
    {
        /**
         * Search :
         *
         * C:\project\...\src\Application\Usecases\User\File.php:64
         *
         * ou
         *
         * /project/src/Application/Usecases/User/File.php:64
         */
        preg_match_all(
            '#([A-Za-z]:)?[\\\\/].*?[\\\\/]src[\\\\/].+?\.php:(\d+)#',
            $trace,
            $matches
        );

        if (empty($matches[0])) {
            return [
                'file' => null,
                'line' => null,
            ];
        }

        $fullMatch = $matches[0][0];
        $line = (int) $matches[2][0];

        $position = strpos(
            str_replace('\\', '/', $fullMatch),
            '/src/'
        );

        if ($position === false) {
            return [
                'file' => null,
                'line' => null,
            ];
        }

        $file = substr(
            str_replace('\\', '/', $fullMatch),
            $position + 1
        );

        // Retire :64
        $file = preg_replace(
            '/:\d+$/',
            '',
            $file
        );

        return [
            'file' => $file,
            'line' => $line,
        ];
    }
}