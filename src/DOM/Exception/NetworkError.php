<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * A network error occurred.
 */
final class NetworkError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::NETWORK_ERR, $previous);
    }
}
