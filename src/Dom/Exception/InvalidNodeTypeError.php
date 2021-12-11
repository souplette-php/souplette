<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

use Throwable;

/**
 * The supplied node is incorrect or has an incorrect ancestor for this operation.
 */
final class InvalidNodeTypeError extends DomException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::INVALID_NODE_TYPE_ERR, $previous);
    }
}
