<?php
declare(strict_types=1);

namespace Szemul\ErrorHandler\Test\Handler;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use RuntimeException;
use Szemul\ErrorHandler\Handler\LoggingErrorHandler;
use Szemul\ErrorHandler\Helper\ErrorHandlerLevelConverter;
use WMDE\PsrLogTestDoubles\LoggerSpy;

class LoggingErrorHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private const ERROR_MESSAGE = 'Test error message';
    private const ERROR_ID      = 'testErrorId';

    private LoggerSpy                                                    $logger;
    private ErrorHandlerLevelConverter|MockInterface|LegacyMockInterface $errorHandlerLevelConverter;
    private LoggingErrorHandler                                          $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger                     = new LoggerSpy();
        $this->errorHandlerLevelConverter = Mockery::mock(ErrorHandlerLevelConverter::class);
        // @phpstan-ignore-next-line
        $this->sut = new LoggingErrorHandler($this->logger, $this->errorHandlerLevelConverter);
    }

    public function testDebugInfo(): void
    {
        $result = $this->sut->__debugInfo();

        $this->assertArrayHasKey('logger', $result);
        $this->assertArrayHasKey('errorLevelConverter', $result);
        $this->assertSame($this->errorHandlerLevelConverter, $result['errorLevelConverter']);
        $this->assertIsString($result['logger']);
        $this->assertStringContainsString(get_class($this->logger), $result['logger']);
    }

    public function testHandleException(): void
    {
        $errorLevel = E_WARNING;
        $file       = __FILE__;
        $line       = __LINE__;

        $logLevel              = LogLevel::WARNING;
        $errorLevelDescription = 'E_WARNING';

        $expectedContext = [
            'tag'      => 'error',
            'type'     => $errorLevelDescription,
            'error_id' => self::ERROR_ID,
        ];

        $this->expectErrorLevelConverted($errorLevel, $logLevel);
        $this->expectErrorLevelDescriptionRetrieved($errorLevel, $errorLevelDescription);

        $this->sut->handleError($errorLevel, self::ERROR_MESSAGE, $file, $line, self::ERROR_ID, false);

        $this->assertCount(1, $this->logger->getLogCalls());

        $logCall = $this->logger->getFirstLogCall();
        $message = $logCall->getMessage();

        $this->assertSame($logLevel, $logCall->getLevel());
        $this->assertEquals($expectedContext, $logCall->getContext());
        $this->assertStringContainsString($errorLevelDescription, $message);
        $this->assertStringContainsString(self::ERROR_MESSAGE, $message);
        $this->assertStringContainsString($file, $message);
        $this->assertStringContainsString((string)$line, $message);
    }

    public function testHandleError(): void
    {
        $exceptionCode = 123;
        $exception     = new RuntimeException(self::ERROR_MESSAGE, $exceptionCode);

        $expectedContext = [
            'tag'      => 'error',
            'type'     => ErrorHandlerLevelConverter::E_EXCEPTION_DESCRIPTION,
            'error_id' => self::ERROR_ID,
        ];

        $this->sut->handleException($exception, self::ERROR_ID);

        $logCall = $this->logger->getFirstLogCall();
        $message = $logCall->getMessage();

        $this->assertSame(LogLevel::ERROR, $logCall->getLevel());
        $this->assertEquals($expectedContext, $logCall->getContext());
        $this->assertStringContainsString(ErrorHandlerLevelConverter::E_EXCEPTION_DESCRIPTION, $message);
        $this->assertStringContainsString(self::ERROR_MESSAGE, $message);
        $this->assertStringContainsString($exception->getFile(), $message);
        $this->assertStringContainsString((string)$exception->getLine(), $message);
        $this->assertStringContainsString((string)$exceptionCode, $message);
    }

    public function testHandleShutdown(): void
    {
        $errorLevel = E_WARNING;
        $file       = __FILE__;
        $line       = __LINE__;

        $logLevel              = LogLevel::WARNING;
        $errorLevelDescription = 'E_WARNING';

        $expectedContext = [
            'tag'      => 'error',
            'type'     => $errorLevelDescription,
            'error_id' => self::ERROR_ID,
        ];

        $this->expectErrorLevelConverted($errorLevel, $logLevel);
        $this->expectErrorLevelDescriptionRetrieved($errorLevel, $errorLevelDescription);

        $this->sut->handleShutdown($errorLevel, self::ERROR_MESSAGE, $file, $line, self::ERROR_ID);

        $this->assertCount(1, $this->logger->getLogCalls());

        $logCall = $this->logger->getFirstLogCall();
        $message = $logCall->getMessage();

        $this->assertSame($logLevel, $logCall->getLevel());
        $this->assertEquals($expectedContext, $logCall->getContext());
        $this->assertStringContainsString($errorLevelDescription, $message);
        $this->assertStringContainsString(self::ERROR_MESSAGE, $message);
        $this->assertStringContainsString($file, $message);
        $this->assertStringContainsString((string)$line, $message);
    }

    private function expectErrorLevelConverted(int $received, string $returned): void
    {
        // @phpstan-ignore-next-line
        $this->errorHandlerLevelConverter->shouldReceive('getLogPriorityForErrorLevel')
            ->once()
            ->with($received)
            ->andReturn($returned);
    }

    private function expectErrorLevelDescriptionRetrieved(int $received, string $returned): void
    {
        // @phpstan-ignore-next-line
        $this->errorHandlerLevelConverter->shouldReceive('getPhpErrorLevelDescription')
            ->once()
            ->with($received)
            ->andReturn($returned);
    }
}
