<?php

namespace Szemul\ErrorHandler\Handler;

use Szemul\ErrorHandler\Exception\UnHandledException;

/**
 * Rethrows the handled error or exception as Unhandled exceptions.
 * Can be useful for testing for example
 */
class ExceptionThrowingErrorHandler implements ErrorHandlerInterface
{
    public function handleError(int $errorLevel, string $message, string $file, int $line, string $errorId, bool $isErrorFatal, array $backTrace = []): void
    {
        throw new UnHandledException("$errorLevel thrown with message $message in $file at $line");
    }

    public function handleException(\Throwable $exception, string $errorId): void
    {
        throw new UnHandledException($exception->getMessage(), $exception->getCode(), $exception);
    }

    public function handleShutdown(int $errorLevel, string $message, string $file, int $line, string $errorId): void
    {
        throw new UnHandledException("$errorLevel thrown with message $message in $file at $line");
    }
}
