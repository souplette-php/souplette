<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

use Throwable;

/**
 * Provided data is inadequate.
 */
final class DataError extends DomException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
