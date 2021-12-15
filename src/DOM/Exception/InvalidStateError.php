<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * The object is in an invalid state.
 */
final class InvalidStateError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::INVALID_STATE_ERR, $previous);
    }
}
