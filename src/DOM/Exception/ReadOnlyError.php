<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * The mutating operation was attempted in a "readonly" transaction.
 */
final class ReadOnlyError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
