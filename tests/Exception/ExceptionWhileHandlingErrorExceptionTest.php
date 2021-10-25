<?php
declare(strict_types=1);

namespace Szemul\ErrorHandler\Test\Exception;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Szemul\ErrorHandler\Exception\ExceptionWhileHandlingErrorException;
use Szemul\ErrorHandler\Handler\ErrorHandlerInterface;
use Throwable;

class ExceptionWhileHandlingErrorExceptionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const ERROR_ID          = 'errorId';
    private const ERROR_MESSAGE     = 'Error happened';
    private const THROWABLE_MESSAGE = 'Throwable message';

    private ErrorHandlerInterface|MockInterface|LegacyMockInterface $errorHandler;
    private Throwable $throwable;
    private ExceptionWhileHandlingErrorException $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->throwable = new RuntimeException(self::THROWABLE_MESSAGE);

        $this->errorHandler = Mockery::mock(ErrorHandlerInterface::class);
        $this->sut          = new ExceptionWhileHandlingErrorException(
            $this->errorHandler, // @phpstan-ignore-line
            $this->throwable,
            self::ERROR_ID,
            self::ERROR_MESSAGE,
        );
    }

    public function testGetMessage(): void
    {
        $message = $this->sut->getMessage();
        $this->assertStringContainsString(self::ERROR_ID, $message);
        $this->assertStringContainsString(self::ERROR_MESSAGE, $message);
        $this->assertStringContainsString(self::THROWABLE_MESSAGE, $message);
        $this->assertStringContainsString(get_class($this->throwable), $message);
        $this->assertStringContainsString(get_class($this->errorHandler), $message);
    }

    public function testGetters(): void
    {
        $this->assertSame($this->errorHandler, $this->sut->getErrorHandler());
        $this->assertSame($this->throwable, $this->sut->getThrowable());
        $this->assertSame(self::ERROR_ID, $this->sut->getErrorId());
        $this->assertSame(self::ERROR_MESSAGE, $this->sut->getErrorMessage());
    }
}
