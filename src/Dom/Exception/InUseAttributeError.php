<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

final class InUseAttributeError extends DomException
{
    public function __construct($message = "", \Throwable $previous = null)
    {
        parent::__construct($message, ErrorCodes::INUSE_ATTRIBUTE_ERROR, $previous);
    }
}
