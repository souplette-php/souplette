<?php declare(strict_types=1);

namespace Souplette\DOM\Traits;

use Souplette\DOM\Element;
use Souplette\DOM\Traversal\ElementTraversal;

/**
 * @see https://dom.spec.whatwg.org/#interface-nonelementparentnode
 */
trait NonElementParentNodeTrait
{
    public function getElementById(string $elementId): ?Element
    {
        if (!$elementId) return null;
        foreach (ElementTraversal::descendantsOf($this) as $node) {
            if ($node->getAttribute('id') === $elementId) {
                return $node;
            }
        }
        return null;
    }
}
