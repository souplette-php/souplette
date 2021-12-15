<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * The string contains invalid characters.
 */
final class InvalidCharacterError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::INVALID_CHARACTER_ERR, $previous);
    }
}
