<?php declare(strict_types=1);

namespace Souplette\DOM\Api;

use Souplette\DOM\Element;

/**
 * Implemented by Document & DocumentFragment
 * @see https://dom.spec.whatwg.org/#interface-nonelementparentnode
 */
interface NonElementParentNodeInterface extends NodeInterface
{
    public function getElementById(string $elementId): ?Element;
}
