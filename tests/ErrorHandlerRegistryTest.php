<?php
declare(strict_types=1);

namespace Szemul\ErrorHandler\Test;

use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Szemul\ErrorHandler\ErrorHandlerRegistry;
use PHPUnit\Framework\TestCase;
use Szemul\ErrorHandler\Handler\ErrorHandlerInterface;
use Szemul\ErrorHandler\Terminator\TerminatorInterface;
use Throwable;

class ErrorHandlerRegistryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const ERROR_MESSAGE = 'error message';

    private ErrorHandlerRegistry                                  $sut;
    private TerminatorInterface|MockInterface|LegacyMockInterface $terminator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->terminator = Mockery::mock(TerminatorInterface::class);
        $this->sut        = new ErrorHandlerRegistry($this->terminator); // @phpstan-ignore-line
    }

    public function testAddAndRemove(): void
    {
        $exception1 = new Exception();
        $exception2 = new Exception();
        $exception3 = new Exception();
        $this->sut->handleException($exception1);

        $handler1 = $this->getHandler();
        $handler2 = $this->getHandler();
        $this->expectExceptionHandled($handler1, $exception1);
        $this->expectExceptionHandled($handler2, $exception1);
        $this->expectExceptionHandled($handler2, $exception2);

        $this->sut->addErrorHandler($handler1);
        $this->sut->addErrorHandler($handler2);

        $this->sut->handleException($exception1);
        $this->sut->removeErrorHandler($handler1);

        $this->sut->handleException($exception2);
        $this->sut->removeErrorHandler($handler2);

        $this->sut->handleException($exception3);

        $this->sut->removeErrorHandler($handler2);
    }

    public function testHandleErrorWithNonFatalError(): void
    {
        $handler = $this->getHandler();
        $this->sut->addErrorHandler($handler);
        $file = __FILE__;
        $line = __LINE__;

        $this->expectErrorHandled($handler, E_USER_WARNING, $file, $line, false);

        $this->sut->handleError(E_USER_WARNING, self::ERROR_MESSAGE, $file, $line);
    }

    public function testHandleErrorWithFatalError(): void
    {
        $handler = $this->getHandler();
        $this->sut->addErrorHandler($handler);
        $file = __FILE__;
        $line = __LINE__;

        $this->expectErrorHandled($handler, E_ERROR, $file, $line, true);
        $this->expectTerminated();

        $this->sut->handleError(E_ERROR, self::ERROR_MESSAGE, $file, $line);
    }

    private function getHandler(): ErrorHandlerInterface|MockInterface|LegacyMockInterface
    {
        return Mockery::mock(ErrorHandlerInterface::class);
    }

    private function expectExceptionHandled(
        ErrorHandlerInterface|MockInterface|LegacyMockInterface $errorHandler,
        Throwable $exception,
    ): void {
        // @phpstan-ignore-next-line
        $errorHandler->shouldReceive('handleException')
            ->once()
            ->with($exception, Mockery::any());
    }

    private function expectErrorHandled(
        ErrorHandlerInterface|MockInterface|LegacyMockInterface $errorHandler,
        int $errorLevel,
        string $file,
        int $line,
        bool $isErrorFatal,
    ): void {
        // @phpstan-ignore-next-line
        $errorHandler->shouldReceive('handleError')
            ->once()
            ->with($errorLevel, self::ERROR_MESSAGE, $file, $line, Mockery::any(), $isErrorFatal, Mockery::any());
    }

    private function expectTerminated(): void
    {
        // @phpstan-ignore-next-line
        $this->terminator->shouldReceive('terminate')
            ->with(TerminatorInterface::EXIT_CODE_FATAL_ERROR);
    }
}
