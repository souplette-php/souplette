<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * The quota has been exceeded.
 */
final class QuotaExceededError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::QUOTA_EXCEEDED_ERR, $previous);
    }
}
