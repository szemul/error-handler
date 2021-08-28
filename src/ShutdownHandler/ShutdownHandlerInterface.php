<?php
declare(strict_types=1);

namespace Szemul\ErrorHandler\ShutdownHandler;

interface ShutdownHandlerInterface
{
    public function handleShutdown(): void;
}
