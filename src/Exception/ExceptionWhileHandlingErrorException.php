<?php
declare(strict_types=1);

namespace Szemul\ErrorHandler\Exception;

use Szemul\ErrorHandler\Handler\ErrorHandlerInterface;
use Throwable;

class ExceptionWhileHandlingErrorException extends ErrorHandlerException
{
    public function __construct(
        private ErrorHandlerInterface $errorHandler,
        private Throwable $throwable,
        private string $errorId,
        private string $errorMessage,
    ) {
        parent::__construct(
            sprintf(
                'Throwable from error handler while processing error. Throwable type: %s, message: %s. '
                    . 'Error handler class: %s. Error ID: %s, original error message: %s',
                get_class($this->throwable),
                $this->throwable->getMessage(),
                get_class($this->errorHandler),
                $this->errorId,
                $this->errorMessage,
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

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
