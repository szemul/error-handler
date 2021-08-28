<?php
declare(strict_types=1);

namespace Szemul\ErrorHandler;

use Szemul\ErrorHandler\ShutdownHandler\ShutdownHandlerInterface;
use Throwable;

class ShutdownHandlerRegistry
{
    /** @var ShutdownHandlerInterface[] */
    private array $shutdownHandlers = [];

    public function register(): static
    {
        register_shutdown_function([$this, 'handleShutdown']);

        return $this;
    }

    public function addShutdownHandler(ShutdownHandlerInterface $shutdownHandler): static
    {
        $this->shutdownHandlers[] = $shutdownHandler;

        return $this;
    }

    public function removeShutdownHandler(ShutdownHandlerInterface $shutdownHandler): static
    {
        $index = array_search($shutdownHandler, $this->shutdownHandlers);
        if (false === $index) {
            return $this;
        }
        unset($this->shutdownHandlers[$index]);

        return $this;
    }

    public function handleShutdown(): void
    {
        foreach ($this->shutdownHandlers as $shutdownHandler) {
            try {
                $shutdownHandler->handleShutdown();
            } catch (Throwable $e) {
                // Nothing we can do with an exception during a shutdown so ignore
            }
        }
    }
}
