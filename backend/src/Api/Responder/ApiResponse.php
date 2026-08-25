<?php


namespace App\Api\Responder;

use App\Domain\ApplicationErrorCode;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\ArrayInput;


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
        ?ApplicationErrorCode $code = null, 
        ?\Throwable $throwable = null,
        int $statusCode = 400,
        ?bool $verbose = true
    ): self
    {
        self::formatAndDisplayStackTrace(
            verbose: $verbose,
            throwable: $throwable
        );

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


    public static function formatAndDisplayStackTrace(bool $verbose, ?\Throwable $throwable): void
    {
        if (!$throwable) {
            return;
        }

        if (self::$logger) {
            self::$logger->error("Caught Exception: " . $throwable->getMessage(), ['exception' => $throwable]);
        }

        if ($verbose) {
            $output = new ConsoleOutput();
            $stderr = $output->getErrorOutput();

            $projectDir = str_replace('\\', '/', dirname(__DIR__, 5));

            // Filter and collect app frames
            $appFrames = [];
            foreach ($throwable->getTrace() as $index => $frame) {
                $file = str_replace('\\', '/', $frame['file'] ?? '');
                if (str_contains($file, '/src/')) {
                    $shortFile = str_replace($projectDir . '/', '', $file);
                    $class = $frame['class'] ?? '';
                    $type = $frame['type'] ?? '';
                    $func = $frame['function'] ?? '';

                    $appFrames[] = [
                        'index' => $index,
                        'call'  => sprintf('%s%s%s()', $class, $type, $func),
                        'file'  => sprintf('%s:%d', $shortFile, $frame['line'] ?? 0),
                    ];
                }
            }

            $file = str_replace('\\', '/', $throwable->getFile());
            $shortFile = str_replace($projectDir . '/', '', $file);
            $exceptionClass = (new \ReflectionClass($throwable))->getShortName();

            // Header block
            $lines = [];
            $lines[] = '';
            $lines[] = sprintf(
                "<bg=red;fg=white;options=bold> %s </> <fg=red;options=bold>%s</> <fg=gray>(Code %s)</>",
                $exceptionClass,
                $throwable->getMessage(),
                $throwable->getCode()
            );
            $lines[] = sprintf("  <fg=gray>┌─ 📍 File:</> <fg=yellow>%s:%d</>", $shortFile, $throwable->getLine());

            // Stack trace tree block
            $frameCount = count($appFrames);
            if ($frameCount > 0) {
                $lines[] = "  <fg=gray>│</>";
                $lines[] = "  <fg=gray>├─ 📜 Stack Trace:</>";

                foreach ($appFrames as $i => $frame) {
                    $isLast = ($i === $frameCount - 1);
                    $treeSymbol = $isLast ? '└─' : '├─';

                    $lines[] = sprintf(
                        "  <fg=gray>│  %s</> <fg=cyan>#%d</> <fg=white;options=bold>%s</> <fg=gray>at</> <fg=yellow>%s</>",
                        $treeSymbol,
                        $frame['index'],
                        $frame['call'],
                        $frame['file']
                    );
                }
            }

            $lines[] = '';

            // Print whole block line by line
            foreach ($lines as $line) {
                $stderr->writeln($line);
            }
        }
    }
}