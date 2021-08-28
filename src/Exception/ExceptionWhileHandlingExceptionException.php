<?php
declare(strict_types=1);

namespace Szemul\ErrorHandler\Exception;

use Szemul\ErrorHandler\Handler\ErrorHandlerInterface;
use Throwable;

class ExceptionWhileHandlingExceptionException extends ErrorHandlerException
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler,
        private Throwable $throwable,
        private string $errorId,
        private Throwable $originalException,
    ) {
        parent::__construct(
            sprintf(
                'Throwable from error handler while processing exception. Throwable type: %s, message: %s. '
                . 'Error handler class: %s. Error ID: %s, original exception type: %s message: %s',
                get_class($this->throwable),
                $this->throwable->getMessage(),
                get_class($this->errorHandler),
                $this->errorId,
                get_class($this->originalException),
                $this->originalException->getMessage(),
            ),
            previous: $this->throwable,
        );
    }

    public function getErrorHandler(): ErrorHandlerInterface
    {
        return $this->errorHandler;
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }

    public function getErrorId(): string
    {
        return $this->errorId;
    }

    public function getOriginalException(): Throwable
    {
        return $this->originalException;
    }
}
