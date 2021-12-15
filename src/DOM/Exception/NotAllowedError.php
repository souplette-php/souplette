<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * The request is not allowed by the user agent or the platform in the current context,
 * possibly because the user denied permission.
 */
final class NotAllowedError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
