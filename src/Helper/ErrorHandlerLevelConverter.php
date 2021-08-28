<?php
declare(strict_types=1);

namespace Szemul\ErrorHandler\Helper;

use Psr\Log\LogLevel;

class ErrorHandlerLevelConverter
{
    /** Code of the error represents an exception. (2^30) */
    public const E_EXCEPTION = 1073741824;

    // Error descriptions
    public const E_ERROR_DESCRIPTION               = 'E_ERROR';
    public const E_WARNING_DESCRIPTION             = 'E_WARNING';
    public const E_PARSE_DESCRIPTION               = 'E_PARSE';
    public const E_NOTICE_DESCRIPTION              = 'E_NOTICE';
    public const E_CORE_ERROR_DESCRIPTION          = 'E_CORE_ERROR';
    public const E_CORE_WARNING_DESCRIPTION        = 'E_CORE_WARNING';
    public const E_COMPILE_ERROR_DESCRIPTION       = 'E_COMPILE_ERROR';
    public const E_COMPILE_WARNING_DESCRIPTION     = 'E_COMPILE_WARNINING';
    public const E_USER_ERROR_DESCRIPTION          = 'E_USER_ERROR';
    public const E_USER_WARNING_DESCRIPTION        = 'E_USER_WARNING';
    public const E_USER_NOTICE_DESCRIPTION         = 'E_USER_NOTICE';
    public const E_STRICT_DESCRIPTION              = 'E_STRICT';
    public const E_RECOVERABLE_ERROR_DESCRIPTION   = 'E_RECOVERABLE_ERROR';
    public const E_DEPRECATED_DESCRIPTION          = 'E_DEPRECATED';
    public const E_USER_DEPRECATED_DESCRIPTION     = 'E_USER_DEPRECATED';
    public const E_EXCEPTION_DESCRIPTION           = 'E_EXCEPTION';
    public const UNKNOWN_DESCRIPTION               = 'UNKNOWN';

    /**
     * Returns the description for the provided error level
     */
    public function getPhpErrorLevelDescription(int $errorLevel): string
    {
        return match ($errorLevel) {
            E_ERROR             => self::E_ERROR_DESCRIPTION,
            E_PARSE             => self::E_PARSE_DESCRIPTION,
            E_WARNING           => self::E_WARNING_DESCRIPTION,
            E_NOTICE            => self::E_NOTICE_DESCRIPTION,
            E_CORE_ERROR        => self::E_CORE_ERROR_DESCRIPTION,
            E_CORE_WARNING      => self::E_CORE_WARNING_DESCRIPTION,
            E_COMPILE_ERROR     => self::E_COMPILE_ERROR_DESCRIPTION,
            E_COMPILE_WARNING   => self::E_COMPILE_WARNING_DESCRIPTION,
            E_STRICT            => self::E_STRICT_DESCRIPTION,
            E_RECOVERABLE_ERROR => self::E_RECOVERABLE_ERROR_DESCRIPTION,
            E_DEPRECATED        => self::E_DEPRECATED_DESCRIPTION,
            E_USER_ERROR        => self::E_USER_ERROR_DESCRIPTION,
            E_USER_WARNING      => self::E_USER_WARNING_DESCRIPTION,
            E_USER_NOTICE       => self::E_USER_NOTICE_DESCRIPTION,
            E_USER_DEPRECATED   => self::E_USER_DEPRECATED_DESCRIPTION,
            self::E_EXCEPTION   => self::E_EXCEPTION_DESCRIPTION,
            default             => self::UNKNOWN_DESCRIPTION,
        };
    }

    /**
     * Returns the applicable log priority for the specified errorlevel
     */
    public function getLogPriorityForErrorLevel(int $errorLevel): string
    {
        return match ($errorLevel) {
            self::E_EXCEPTION, E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR => LogLevel::ERROR,
            E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING => LogLevel::WARNING,
            default => LogLevel::NOTICE,
        };
    }
}
