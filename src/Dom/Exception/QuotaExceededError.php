<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

use Throwable;

/**
 * The quota has been exceeded.
 */
final class QuotaExceededError extends DomException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::QUOTA_EXCEEDED_ERR, $previous);
    }
}
