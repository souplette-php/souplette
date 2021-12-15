<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * The encoding operation (either encoded or decoding) failed.
 */
final class EncodingError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
