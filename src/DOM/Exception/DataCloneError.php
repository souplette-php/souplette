<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * The object can not be cloned.
 */
final class DataCloneError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::DATA_CLONE_ERR, $previous);
    }
}
