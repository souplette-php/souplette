<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * @deprecated Use `TypeError` for invalid arguments,
 * `NotSupportedError` DOMException for unsupported operations,
 * and `NotAllowedError` DOMException for denied requests instead.
 */
final class InvalidAccessError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::INVALID_ACCESS_ERR, $previous);
    }
}
