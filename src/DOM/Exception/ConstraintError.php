<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * A mutation operation in a transaction failed because a constraint was not satisfied.
 */
final class ConstraintError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
