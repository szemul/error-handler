<?php

declare(strict_types=1);

namespace Szemul\ErrorHandler\Test;

use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Szemul\ErrorHandler\ErrorHandlerRegistry;
use Szemul\ErrorHandler\Exception\ExceptionWhileHandlingExceptionException;
use Szemul\ErrorHandler\Exception\UnHandledException;
use Szemul\ErrorHandler\Handler\ErrorHandlerInterface;
use Szemul\ErrorHandler\Helper\ErrorIdGenerator;
use Szemul\ErrorHandler\Terminator\TerminatorInterface;
use Throwable;

class ErrorHandlerRegistryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const ERROR_MESSAGE = 'error message';
    private const ERROR_ID      = 'errorId';

    private ErrorHandlerRegistry                                  $sut;
    private TerminatorInterface|MockInterface|LegacyMockInterface $terminator;
    private ErrorIdGenerator|MockInterface|LegacyMockInterface    $errorIdGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->terminator       = Mockery::mock(TerminatorInterface::class);
        $this->errorIdGenerator = Mockery::mock(ErrorIdGenerator::class);

        $this->sut = new ErrorHandlerRegistry($this->terminator, $this->errorIdGenerator); // @phpstan-ignore-line
    }

    public function testAddAndRemove(): void
    {
        $exception1 = new Exception('exception1');
        $exception2 = new Exception('exception2');
        $exception3 = new Exception('exception3');
        $this->sut->handleException($exception1);

        $handler1 = $this->getHandler();
        $handler2 = $this->getHandler();
        $this->expectExceptionHandled($handler1, $exception1);
        $this->expectExceptionHandled($handler2, $exception1);
        $this->expectExceptionHandled($handler2, $exception2);
        $this->expectErrorIdGenerated($exception1->getMessage(), $exception1->getFile(), $exception1->getLine());
        $this->expectErrorIdGenerated($exception2->getMessage(), $exception2->getFile(), $exception2->getLine());

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

        $this->expectErrorIdGenerated(self::ERROR_MESSAGE, $file, $line);
        $this->expectErrorHandled($handler, E_USER_WARNING, $file, $line, false);

        $this->sut->handleError(E_USER_WARNING, self::ERROR_MESSAGE, $file, $line);
    }

    public function testHandleErrorWithFatalError(): void
    {
        $handler = $this->getHandler();
        $this->sut->addErrorHandler($handler);
        $file = __FILE__;
        $line = __LINE__;

        $this->expectErrorIdGenerated(self::ERROR_MESSAGE, $file, $line);
        $this->expectErrorHandled($handler, E_ERROR, $file, $line, true);
        $this->expectTerminated();

        $this->sut->handleError(E_ERROR, self::ERROR_MESSAGE, $file, $line);
    }

    public function testHandleException(): void
    {
        $errorHandler = $this->getHandler();
        $exception    = new Exception('test');

        $this->sut->addErrorHandler($errorHandler);
        $this->expectErrorIdGenerated($exception->getMessage(), $exception->getFile(), $exception->getLine());
        $this->expectExceptionHandled($errorHandler, $exception);

        $this->sut->handleException($exception);
    }

    public function testHandleExceptionWhenUnhandledExceptionThrown_shouldReThrow(): void
    {
        $errorHandler     = $this->getHandler();
        $exception        = new Exception('test');
        $handlerException = new UnHandledException('unhandled');

        $this->sut->addErrorHandler($errorHandler);
        $this->expectErrorIdGenerated($exception->getMessage(), $exception->getFile(), $exception->getLine());
        $this->expectExceptionHandlerThrowsException($errorHandler, $exception, $handlerException);

        $this->expectExceptionObject($handlerException);
        $this->sut->handleException($exception);
    }

    public function testHandleExceptionWhenExceptionThrown_shouldReThrowAsHandlerException(): void
    {
        $errorHandler     = $this->getHandler();
        $exception        = new Exception('test');
        $handlerException = new Exception('handler');

        $this->sut->addErrorHandler($errorHandler);
        $this->expectErrorIdGenerated($exception->getMessage(), $exception->getFile(), $exception->getLine());
        $this->expectExceptionHandlerThrowsException($errorHandler, $exception, $handlerException);

        $this->expectErrorIdGeneratedForHandlerException();
        $this->expectHandlerExceptionHandled($errorHandler);

        $this->sut->handleException($exception);
    }

    private function getHandler(): ErrorHandlerInterface|MockInterface|LegacyMockInterface
    {
        return Mockery::mock(ErrorHandlerInterface::class);
    }

    private function expectExceptionHandled(
        ErrorHandlerInterface|MockInterface $errorHandler,
        Throwable $exception,
    ): void {
        // @phpstan-ignore-next-line
        $errorHandler
            ->expects('handleException')
            ->ordered()
            ->with($exception, self::ERROR_ID);
    }

    private function expectHandlerExceptionHandled(
        ErrorHandlerInterface|MockInterface $errorHandler,
    ): void {
        // @phpstan-ignore-next-line
        $errorHandler
            ->expects('handleException')
            ->ordered()
            ->with(
                Mockery::on(function (Throwable $throwable) {
                    return $throwable instanceof ExceptionWhileHandlingExceptionException;
                }),
                self::ERROR_ID,
            );
    }

    private function expectExceptionHandlerThrowsException(
        ErrorHandlerInterface|MockInterface $errorHandler,
        Throwable $exception,
        Throwable $exceptionToThrow,
    ): void {
        // @phpstan-ignore-next-line
        $errorHandler
            ->expects('handleException')
            ->ordered()
            ->with($exception, self::ERROR_ID)
            ->andThrow($exceptionToThrow);
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
            ->with($errorLevel, self::ERROR_MESSAGE, $file, $line, self::ERROR_ID, $isErrorFatal, Mockery::any());
    }

    private function expectTerminated(): void
    {
        // @phpstan-ignore-next-line
        $this->terminator->expects('terminate')
            ->with(TerminatorInterface::EXIT_CODE_FATAL_ERROR);
    }

    private function expectErrorIdGenerated(string $message, string $file, int $line): void
    {
        // @phpstan-ignore-next-line
        $this->errorIdGenerator->expects('generateErrorId')
            ->ordered()
            ->with($message, $file, $line)
            ->andReturn(self::ERROR_ID);
    }

    private function expectErrorIdGeneratedForHandlerException(): void
    {
        // @phpstan-ignore-next-line
        $this->errorIdGenerator->expects('generateErrorId')
            ->ordered()
            ->andReturn(self::ERROR_ID);
    }
}
