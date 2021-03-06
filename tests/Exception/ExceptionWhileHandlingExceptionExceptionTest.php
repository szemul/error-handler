<?php
declare(strict_types=1);

namespace Szemul\ErrorHandler\Test\Exception;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Szemul\ErrorHandler\Exception\ExceptionWhileHandlingExceptionException;
use Szemul\ErrorHandler\Handler\ErrorHandlerInterface;
use Throwable;

class ExceptionWhileHandlingExceptionExceptionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const ERROR_ID                   = 'errorId';
    private const THROWABLE_MESSAGE          = 'Throwable message';
    private const ORIGINAL_EXCEPTION_MESSAGE = 'Original exception message';

    private ErrorHandlerInterface|MockInterface|LegacyMockInterface $errorHandler;
    private Throwable $throwable;
    private Throwable $originalException;
    private ExceptionWhileHandlingExceptionException $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->throwable         = new RuntimeException(self::THROWABLE_MESSAGE);
        $this->originalException = new RuntimeException(self::ORIGINAL_EXCEPTION_MESSAGE);

        $this->errorHandler = Mockery::mock(ErrorHandlerInterface::class);
        $this->sut          = new ExceptionWhileHandlingExceptionException(
            $this->errorHandler, // @phpstan-ignore-line
            $this->throwable,
            self::ERROR_ID,
            $this->originalException,
        );
    }

    public function testGetMessage(): void
    {
        $message = $this->sut->getMessage();
        $this->assertStringContainsString(self::ERROR_ID, $message);
        $this->assertStringContainsString(self::ORIGINAL_EXCEPTION_MESSAGE, $message);
        $this->assertStringContainsString(get_class($this->originalException), $message);
        $this->assertStringContainsString(self::THROWABLE_MESSAGE, $message);
        $this->assertStringContainsString(get_class($this->throwable), $message);
        $this->assertStringContainsString(get_class($this->errorHandler), $message);
    }

    public function testGetters(): void
    {
        $this->assertSame($this->errorHandler, $this->sut->getErrorHandler());
        $this->assertSame($this->throwable, $this->sut->getThrowable());
        $this->assertSame(self::ERROR_ID, $this->sut->getErrorId());
        $this->assertSame($this->originalException, $this->sut->getOriginalException());
    }
}
