<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

use Throwable;

/**
 * An attempt was made to open a database using a lower version than the existing version.
 */
final class VersionError extends DomException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
