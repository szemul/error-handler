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

class ErrorHandlerRegistryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

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

    private function getHandler(): ErrorHandlerInterface|MockInterface|LegacyMockInterface
    {
        return Mockery::mock(ErrorHandlerInterface::class);
    }

    private function expectExceptionHandled(ErrorHandlerInterface|MockInterface|LegacyMockInterface $errorHandler, Exception $exception): void
    {
        // @phpstan-ignore-next-line
        $errorHandler->shouldReceive('handleException')
            ->once()
            ->with($exception, Mockery::any());
    }
}
