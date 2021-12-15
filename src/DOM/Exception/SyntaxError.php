<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * The string did not match the expected pattern.
 */
final class SyntaxError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::SYNTAX_ERR, $previous);
    }
}
