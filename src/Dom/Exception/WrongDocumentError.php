<?php declare(strict_types=1);

namespace Souplette\Dom\Exception;

use Throwable;

/**
 * The object is in the wrong document.
 * @see https://dom.spec.whatwg.org/#concept-document
 */
final class WrongDocumentError extends DomException
{
    public function __construct(string $message = '', ?Throwable $previous = null)
    {
        parent::__construct($message, self::WRONG_DOCUMENT_ERR, $previous);
    }
}
