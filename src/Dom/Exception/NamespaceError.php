<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

use Throwable;

/**
 * The operation is not allowed by Namespaces in XML.
 * @see https://www.w3.org/TR/xml-names/
 */
final class NamespaceError extends DomException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::NAMESPACE_ERR, $previous);
    }
}
