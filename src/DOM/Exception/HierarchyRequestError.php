<?php declare(strict_types=1);

namespace Souplette\DOM\Exception;

use Throwable;

/**
 * The operation would yield an incorrect node tree.
 * @see https://dom.spec.whatwg.org/#concept-node-tree
 */
final class HierarchyRequestError extends DOMException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::HIERARCHY_REQUEST_ERR, $previous);
    }
}
