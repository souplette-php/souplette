<?php declare(strict_types=1);

namespace Souplette\Dom\Api;

use DOMElement;

/**
 * Implemented by: Document, DocumentFragment
 * @see https://dom.spec.whatwg.org/#interface-nonelementparentnode
 */
interface NonElementParentNodeInterface
{
    public function getElementById(string $id): ?DOMElement;
}
