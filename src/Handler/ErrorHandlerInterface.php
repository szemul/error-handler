<?php
declare(strict_types=1);

namespace Szemul\ErrorHandler\Handler;

use Throwable;

interface ErrorHandlerInterface
{
    /**
     * @param array<int,mixed[]> $backTrace
     */
    public function handleError(
        int $errorLevel,
        string $message,
        string $file,
        int $line,
        string $errorId,
        bool $isErrorFatal,
        array $backTrace = [],
    ): void;

    public function handleException(Throwable $exception, string $errorId): void;

    public function handleShutdown(int $errorLevel, string $message, string $file, int $line, string $errorId): void;
}
