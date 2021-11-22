<?php declare(strict_types=1);

namespace Souplette\Dom\Api;

use DOMElement;
use DOMNode;

/**
 * @see https://dom.spec.whatwg.org/#parentnode
 *
 * @property-read DOMElement[] $children
 */
interface ParentNodeInterface extends \DOMParentNode
{
    /**
     * Returns the first element that is a descendant of node that matches selectors.
     */
    public function querySelector(string $selector): ?DOMElement;

    /**
     * Returns all element descendants of node that match selectors.
     *
     * @param string $selector
     * @return DOMElement[]
     */
    public function querySelectorAll(string $selector): iterable;

    /**
     * @return DOMElement[]
     */
    public function getChildren(): iterable;

    public function replaceChildren(DOMNode|string|null ...$nodes): void;
}
