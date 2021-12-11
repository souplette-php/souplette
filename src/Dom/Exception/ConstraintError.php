<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

use Throwable;

/**
 * A mutation operation in a transaction failed because a constraint was not satisfied.
 */
final class ConstraintError extends DomException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
