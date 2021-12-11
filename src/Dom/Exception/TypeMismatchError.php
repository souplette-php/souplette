<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

use Throwable;

/**
 * @deprecated Use `TypeError` instead.
 */
final class TypeMismatchError extends DomException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::TYPE_MISMATCH_ERR, $previous);
    }
}
