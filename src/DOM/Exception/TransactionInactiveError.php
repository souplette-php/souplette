<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * A request was placed against a transaction which is currently not active, or which is finished.
 */
final class TransactionInactiveError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
