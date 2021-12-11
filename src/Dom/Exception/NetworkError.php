<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

use Throwable;

/**
 * A network error occurred.
 */
final class NetworkError extends DomException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::NETWORK_ERR, $previous);
    }
}
