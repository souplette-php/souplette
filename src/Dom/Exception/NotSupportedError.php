<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

final class NotSupportedError extends DomException
{
    public function __construct($message = "", \Throwable $previous = null)
    {
        parent::__construct($message, ErrorCodes::NOT_SUPPORTED_ERROR, $previous);
    }
}
