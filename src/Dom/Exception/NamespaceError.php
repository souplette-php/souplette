<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

final class NamespaceError extends DomException
{
    public function __construct($message = "", \Throwable $previous = null)
    {
        parent::__construct($message, ErrorCodes::NAMESPACE_ERROR, $previous);
    }
}
