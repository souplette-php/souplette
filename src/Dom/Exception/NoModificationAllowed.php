<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

use Throwable;

final class NoModificationAllowed extends DomException
{
    public function __construct($message = "", Throwable $previous = null)
    {
        parent::__construct($message, ErrorCodes::NO_MODIFICATION_ALLOWED_ERROR, $previous);
    }
}
