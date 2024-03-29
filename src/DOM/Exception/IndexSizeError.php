<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * @deprecated Use RangeError instead.
 */
final class IndexSizeError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::INDEX_SIZE_ERR, $previous);
    }
}
