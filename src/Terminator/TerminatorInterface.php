<?php
declare(strict_types=1);

namespace Szemul\ErrorHandler\Terminator;

use JetBrains\PhpStorm\NoReturn;

interface TerminatorInterface
{
    public const EXIT_CODE_FATAL_ERROR        = 254;
    public const EXIT_CODE_UNCAUGHT_EXCEPTION = 253;
    public const EXIT_CODE_SIGNAL_ABORT       = 252;
    public const EXIT_CODE_OK                 = 0;

    /**
     * @return never-return
     */
    #[NoReturn]
    public function terminate(int $exitCode): void;
}
