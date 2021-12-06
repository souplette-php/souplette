<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

final class InUseAttributeError extends DomException
{
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct(
            'The node provided is an attribute node that is already an attribute of another element.'
                . ' Attribute nodes must be explicitly cloned.',
            ErrorCodes::INUSE_ATTRIBUTE_ERROR,
            $previous
        );
    }
}
