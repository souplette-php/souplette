<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

use Throwable;

final class NotFoundError extends DomException
{
    public function __construct($message = "", Throwable $previous = null)
    {
        parent::__construct($message, ErrorCodes::NOT_FOUND_ERROR, $previous);
    }
}
