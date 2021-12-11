<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

use Throwable;

/**
 * The object can not be modified in this way.
 */
final class InvalidModificationError extends DomException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::INVALID_MODIFICATION_ERR, $previous);
    }
}
