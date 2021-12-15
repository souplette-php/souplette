<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * The operation timed out.
 */
final class TimeoutError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::TIMEOUT_ERR, $previous);
    }
}
