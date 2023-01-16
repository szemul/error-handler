<?php

namespace Szemul\ErrorHandler\Test;

use Szemul\ErrorHandler\ErrorHandlerRegistry;

class ErrorHandlerRegistryMock extends ErrorHandlerRegistry
{
    public function __construct()
    {
    }

    public function handleError(int $errorLevel, string $message, string $file, int $line): bool
    {
        throw new \Exception("[$errorLevel] $message in $file at $line");
    }

    public function handleException(\Throwable $exception): void
    {
        throw $exception;
    }
}
