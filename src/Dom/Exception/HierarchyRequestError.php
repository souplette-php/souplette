<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

use Throwable;

final class HierarchyRequestError extends DomException
{
    public function __construct($message = "", Throwable $previous = null)
    {
        parent::__construct($message, ErrorCodes::HIERARCHY_REQUEST_ERROR, $previous);
    }
}
