<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

use Throwable;

/**
 * The operation failed for an unknown transient reason (e.g. out of memory).
 */
final class UnknownError extends DomException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
