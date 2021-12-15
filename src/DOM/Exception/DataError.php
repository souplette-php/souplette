<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * Provided data is inadequate.
 */
final class DataError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
