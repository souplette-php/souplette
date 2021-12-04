<?php declare(strict_types=1);

namespace Souplette\Dom\Api;

use DOMNode;
use Souplette\Dom\Legacy\Element;

/**
 * @see https://dom.spec.whatwg.org/#parentnode
 *
 * @property-read Element|null $firstElementChild
 * @property-read Element|null $lastElementChild
 * @property-read Element[] $children
 */
interface ParentNodeInterface extends \DOMParentNode
{
    /**
     * Returns the first element that is a descendant of node that matches selectors.
     */
    public function querySelector(string $selector): ?Element;

    /**
     * Returns all element descendants of node that match selectors.
     *
     * @return Element[]
     */
    public function querySelectorAll(string $selector): iterable;

    /**
     * @return Element[]
     */
    public function getChildren(): iterable;

    public function replaceChildren(DOMNode|string|null ...$nodes): void;
}
