<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

use Throwable;

/**
 * The operation is not supported.
 */
final class NotSupportedError extends DomException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::NOT_SUPPORTED_ERR, $previous);
    }
}
