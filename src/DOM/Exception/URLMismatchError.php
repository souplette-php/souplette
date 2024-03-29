<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * The given URL does not match another URL.
 */
final class URLMismatchError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::URL_MISMATCH_ERR, $previous);
    }
}
