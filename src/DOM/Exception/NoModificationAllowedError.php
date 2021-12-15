<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * The object can not be modified.
 */
final class NoModificationAllowedError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::NO_MODIFICATION_ALLOWED_ERR, $previous);
    }
}
