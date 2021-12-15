<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * The attribute is in use.
 */
final class InUseAttributeError extends DOMException
{
    private const DEFAULT_MSG = <<<'ERR'
    The node provided is an attribute node that is already an attribute of another element. \
    Attribute nodes must be explicitly cloned.
    ERR;

    public function __construct(string $message = self::DEFAULT_MSG, ?Throwable $previous = null)
    {
        parent::__construct($message, self::INUSE_ATTRIBUTE_ERR, $previous);
    }
}
