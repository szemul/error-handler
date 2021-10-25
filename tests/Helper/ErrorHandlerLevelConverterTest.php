<?php
declare(strict_types=1);

namespace Szemul\ErrorHandler\Test\Helper;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use Szemul\ErrorHandler\Helper\ErrorHandlerLevelConverter;

class ErrorHandlerLevelConverterTest extends TestCase
{

    /**
     * @dataProvider getErrorLevelDescriptions
     */
    public function testGetPhpErrorLevelDescription(int $level, string $expected): void
    {
        $this->assertSame($expected, (new ErrorHandlerLevelConverter())->getPhpErrorLevelDescription($level));
    }

    /**
     * @dataProvider getLogPriorityLevels
     */
    public function testGetLogPriorityForErrorLevel(int $level, string $expected): void
    {
        $this->assertSame($expected, (new ErrorHandlerLevelConverter())->getLogPriorityForErrorLevel($level));
    }

    /**
     * @return array[]
     */
    public function getErrorLevelDescriptions(): array
    {
        return [
            [E_ERROR, ErrorHandlerLevelConverter::E_ERROR_DESCRIPTION],
            [E_PARSE, ErrorHandlerLevelConverter::E_PARSE_DESCRIPTION],
            [E_WARNING, ErrorHandlerLevelConverter::E_WARNING_DESCRIPTION],
            [E_NOTICE, ErrorHandlerLevelConverter::E_NOTICE_DESCRIPTION],
            [E_CORE_ERROR, ErrorHandlerLevelConverter::E_CORE_ERROR_DESCRIPTION],
            [E_CORE_WARNING, ErrorHandlerLevelConverter::E_CORE_WARNING_DESCRIPTION],
            [E_COMPILE_ERROR, ErrorHandlerLevelConverter::E_COMPILE_ERROR_DESCRIPTION],
            [E_COMPILE_WARNING, ErrorHandlerLevelConverter::E_COMPILE_WARNING_DESCRIPTION],
            [E_STRICT, ErrorHandlerLevelConverter::E_STRICT_DESCRIPTION],
            [E_RECOVERABLE_ERROR, ErrorHandlerLevelConverter::E_RECOVERABLE_ERROR_DESCRIPTION],
            [E_DEPRECATED, ErrorHandlerLevelConverter::E_DEPRECATED_DESCRIPTION],
            [E_USER_ERROR, ErrorHandlerLevelConverter::E_USER_ERROR_DESCRIPTION],
            [E_USER_WARNING, ErrorHandlerLevelConverter::E_USER_WARNING_DESCRIPTION],
            [E_USER_NOTICE, ErrorHandlerLevelConverter::E_USER_NOTICE_DESCRIPTION],
            [E_USER_DEPRECATED, ErrorHandlerLevelConverter::E_USER_DEPRECATED_DESCRIPTION],
            [ErrorHandlerLevelConverter::E_EXCEPTION, ErrorHandlerLevelConverter::E_EXCEPTION_DESCRIPTION],
            [-1, ErrorHandlerLevelConverter::UNKNOWN_DESCRIPTION],
        ];
    }

    /**
     * @return array[]
     */
    public function getLogPriorityLevels(): array
    {
        return [
            [E_ERROR, LogLevel::ERROR],
            [E_PARSE, LogLevel::ERROR],
            [E_WARNING, LogLevel::WARNING],
            [E_NOTICE, LogLevel::NOTICE],
            [E_CORE_ERROR, LogLevel::ERROR],
            [E_CORE_WARNING, LogLevel::WARNING],
            [E_COMPILE_ERROR, LogLevel::ERROR],
            [E_COMPILE_WARNING, LogLevel::WARNING],
            [E_STRICT, LogLevel::NOTICE],
            [E_RECOVERABLE_ERROR, LogLevel::ERROR],
            [E_DEPRECATED, LogLevel::NOTICE],
            [E_USER_ERROR, LogLevel::ERROR],
            [E_USER_WARNING, LogLevel::WARNING],
            [E_USER_NOTICE, LogLevel::NOTICE],
            [E_USER_DEPRECATED, LogLevel::NOTICE],
        ];
    }
}
