<?php
declare(strict_types=1);

namespace Szemul\ErrorHandler\Terminator;

use JetBrains\PhpStorm\NoReturn;

class BasicTerminator implements TerminatorInterface
{
    /** @codeCoverageIgnore  */
    #[NoReturn]
    public function terminate(int $exitCode): void
    {
        exit($exitCode);
    }
}
