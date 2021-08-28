<?php
declare(strict_types=1);

namespace Szemul\ErrorHandler;

use Szemul\ErrorHandler\Exception\ErrorHandlerException;
use Szemul\ErrorHandler\Exception\ExceptionWhileHandlingErrorException;
use Szemul\ErrorHandler\Exception\ExceptionWhileHandlingExceptionException;
use Szemul\ErrorHandler\Handler\ErrorHandlerInterface;
use Szemul\ErrorHandler\ShutdownHandler\ShutdownHandlerInterface;
use Szemul\ErrorHandler\Terminator\TerminatorInterface;
use Throwable;

class ErrorHandlerRegistry implements ShutdownHandlerInterface
{
    /** @var ErrorHandlerInterface[] */
    protected array   $errorHandlers      = [];
    protected ?string $lastHandledErrorId = null;
    protected bool    $isRegistered       = false;

    public function __construct(protected TerminatorInterface $terminator, protected int $errorHandlingIdTimeout = 600)
    {
    }

    /** @codeCoverageIgnore  */
    public function __destruct()
    {
        $this->unregister();
    }

    /** @codeCoverageIgnore  */
    public function register(): static
    {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        $this->isRegistered = true;

        return $this;
    }

    /** @codeCoverageIgnore  */
    public function unregister(): static
    {
        if ($this->isRegistered) {
            restore_error_handler();
            restore_exception_handler();
            $this->isRegistered = false;
        }

        return $this;
    }

    public function addErrorHandler(ErrorHandlerInterface $errorHandler): static
    {
        $this->errorHandlers[] = $errorHandler;

        return $this;
    }

    /**
     * Removes an error handler from the container.
     */
    public function removeErrorHandler(ErrorHandlerInterface $errorHandler): static
    {
        $index = array_search($errorHandler, $this->errorHandlers);
        if (false === $index) {
            return $this;
        }
        unset($this->errorHandlers[$index]);

        return $this;
    }

    public function handleError(int $errorLevel, string $message, string $file, int $line): bool
    {
        $errorReporting = error_reporting();

        if (!($errorLevel & $errorReporting)) {
            // The error should not be reported
            return false;
        }

        if (empty($this->errorHandlers)) {
            // We have no error handlers, let the standard PHP error handler handle it
            return false;
        }

        $isErrorFatal = $this->isErrorFatal($errorLevel);

        $errorId = $this->generateErrorId($message, $file, $line);

        $backTrace = debug_backtrace();
        // We are the first element, remove it from the trace
        array_shift($backTrace);

        $returnValue = true;

        foreach ($this->errorHandlers as $errorHandler) {
            try {
                $errorHandler->handleError($errorLevel, $message, $file, $line, $errorId, $isErrorFatal, $backTrace);
            } catch (Throwable $e) {
                $this->handleException(
                    new ExceptionWhileHandlingErrorException($errorHandler, $e, $errorId, $message),
                );
                $returnValue = false;
            }
        }

        if ($isErrorFatal) {
            $this->unregister();
            $this->terminator->terminate(TerminatorInterface::EXIT_CODE_FATAL_ERROR);
        }

        return $returnValue;
    }

    public function handleException(Throwable $handledException): void
    {
        if (empty($this->errorHandlers)) {
            return;
        }

        $errorId = $this->generateErrorId($handledException->getMessage(), $handledException->getFile(), $handledException->getLine());

        foreach ($this->errorHandlers as $errorHandler) {
            try {
                $errorHandler->handleException($handledException, $errorId);
            } catch (Throwable $thrownException) {
                if (!($handledException instanceof ErrorHandlerException)) {
                    $this->handleException(
                        new ExceptionWhileHandlingExceptionException(
                            $errorHandler,
                            $thrownException,
                            $errorId,
                            $handledException,
                        ),
                    );
                }
            }
        }
    }

    /** @codeCoverageIgnore */
    public function handleShutdown(): void
    {
        $error = error_get_last();

        if (!$error || !$this->isErrorFatal($error['type'])) {
            // Normal shutdown
            return;
        }

        // We are shutting down because of a fatal error, if any more errors occur, they should be handled by
        // the default error handler.
        $this->unregister();

        // Shutdown because of a fatal error
        if (empty($this->errorHandlers)) {
            return;
        }

        $errorId = $this->generateErrorId($error['message'], $error['file'], $error['line']);

        if ($errorId !== $this->lastHandledErrorId) {
            // Make sure that this error has not been already handled
            try {
                foreach ($this->errorHandlers as $errorHandler) {
                    $errorHandler->handleShutdown(
                        $error['type'],
                        $error['message'],
                        $error['file'],
                        $error['line'],
                        $errorId,
                    );
                }
            } catch (Throwable $e) {
                $this->handleException(
                    new ExceptionWhileHandlingErrorException($errorHandler, $e, $errorId, $error['message']),
                );
            }
        }

        $this->terminator->terminate(TerminatorInterface::EXIT_CODE_FATAL_ERROR);
    }

    /**
     * Returns an error ID based on the message, file, line, hostname and current time.
     */
    protected function generateErrorId(string $message, string $file, int $line): string
    {
        if (0 == $this->errorHandlingIdTimeout) {
            return md5($message . $file . $line . php_uname('n'));
        } elseif ($this->errorHandlingIdTimeout < 0) {
            return md5($message . $file . $line . php_uname('n') . uniqid(''));
        } else {
            // @codeCoverageIgnoreStart
            return md5($message . $file . $line . php_uname('n') . floor(time() / $this->errorHandlingIdTimeout));
            // @codeCoverageIgnoreEnd
        }
    }

    protected function isErrorFatal(int $errorLevel): bool
    {
        switch ($errorLevel) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_RECOVERABLE_ERROR:
            case E_USER_ERROR:
                return true;
        }

        return false;
    }
}
