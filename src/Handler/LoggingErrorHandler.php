<?php
declare(strict_types=1);

namespace Szemul\ErrorHandler\Handler;

use Psr\Log\LoggerInterface;
use Szemul\ErrorHandler\Helper\ErrorHandlerLevelConverter;
use Throwable;

class LoggingErrorHandler implements ErrorHandlerInterface
{
    public function __construct(
        protected LoggerInterface $logger,
        private ErrorHandlerLevelConverter $errorLevelConverter,
    ) {
    }

    /**
     * @return array<string,mixed>|null
     */
    public function __debugInfo(): ?array
    {
        return [
            'logger'              => '** Instance of ' . get_class($this->logger),
            'errorLevelConverter' => $this->errorLevelConverter,
        ];
    }

    public function handleError(
        int $errorLevel,
        string $message,
        string $file,
        int $line,
        string $errorId,
        bool $isErrorFatal,
        array $backTrace = [],
    ): void {
        $errorLevelDescription = $this->errorLevelConverter->getPhpErrorLevelDescription($errorLevel);

        $errorMessage = '[' . $errorLevelDescription . '(' . $errorLevel . ')]: ' . $message . ' on line ' . $line
            . ' in ' . $file;

        $this->logger->log(
            $this->errorLevelConverter->getLogPriorityForErrorLevel($errorLevel),
            $errorMessage,
            [
                'tag'      => 'error',
                'type'     => $errorLevelDescription,
                'error_id' => $errorId,
            ],
        );
    }

    public function handleException(Throwable $exception, string $errorId): void
    {
        $errorMessage = '[' . ErrorHandlerLevelConverter::E_EXCEPTION_DESCRIPTION . ']: Unhandled '
            . get_class($exception) . ': ' . $exception->getMessage() . '(' . $exception->getCode() . ') on line '
            . $exception->getLine() . ' in ' . $exception->getFile();

        $this->logger->error(
            $errorMessage,
            [
                'tag'      => 'error',
                'type'     => ErrorHandlerLevelConverter::E_EXCEPTION_DESCRIPTION,
                'error_id' => $errorId,
            ],
        );
    }

    public function handleShutdown(int $errorLevel, string $message, string $file, int $line, string $errorId): void
    {
        $this->handleError($errorLevel, $message, $file, $line, $errorId, true);
    }
}
