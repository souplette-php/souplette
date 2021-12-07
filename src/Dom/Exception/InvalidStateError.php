<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

use Throwable;

final class InvalidStateError extends DomException
{
    public function __construct($message = "", Throwable $previous = null)
    {
        parent::__construct($message, ErrorCodes::INVALID_STATE_ERROR, $previous);
    }
}
