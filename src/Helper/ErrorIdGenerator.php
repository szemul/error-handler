<?php
declare(strict_types=1);

namespace Szemul\ErrorHandler\Helper;

class ErrorIdGenerator
{
    public function __construct(protected int $errorHandlingIdTimeout)
    {
    }

    /**
     * Returns an error ID based on the message, file, line, hostname and current time.
     */
    public function generateErrorId(string $message, string $file, int $line, ?int $currentTimestamp = null): string
    {
        $currentTimestamp = $currentTimestamp ?? time();

        if (0 == $this->errorHandlingIdTimeout) {
            return md5($message . $file . $line . php_uname('n'));
        } elseif ($this->errorHandlingIdTimeout < 0) {
            return md5($message . $file . $line . php_uname('n') . uniqid(''));
        } else {
            return md5($message . $file . $line . php_uname('n') . floor($currentTimestamp / $this->errorHandlingIdTimeout));
        }
    }
}
