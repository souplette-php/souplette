<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * The operation is not supported.
 */
final class NotSupportedError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::NOT_SUPPORTED_ERR, $previous);
    }
}
