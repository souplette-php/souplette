<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Api;

use DOMElement;
use DOMNode;

/**
 * @see https://dom.spec.whatwg.org/#parentnode
 *
 * @property-read DOMElement[] $children
 * @property-read ?DOMElement $firstElementChild
 * @property-read ?DOMElement $lastElementChild
 */
interface ParentNodeInterface
{
    /**
     * Inserts nodes before the first child of node, while replacing strings in nodes with equivalent Text nodes.
     *
     * @param DOMNode|string ...$nodes
     */
    public function prepend(...$nodes): void;
    /**
     * Inserts nodes after the last child of node, while replacing strings in nodes with equivalent Text nodes.
     *
     * @param DOMNode|string ...$nodes
     */
    public function append(...$nodes): void;
    /**
     * Returns the first element that is a descendant of node that matches selectors.
     *
     * @param string $selector
     * @return DOMElement|null
     */
    public function querySelector(string $selector): ?DOMElement;
    /**
     * Returns all element descendants of node that match selectors.
     *
     * @param string $selector
     * @return DOMElement[]
     */
    public function querySelectorAll(string $selector);
    /**
     * @return DOMElement[]
     */
    public function getChildren();
    /**
     * Returns the first child that is an element, and null otherwise.
     *
     * @return DOMElement|null
     */
    public function getFirstElementChild(): ?DOMElement;
    /**
     * Returns the last child that is an element, and null otherwise.
     *
     * @return DOMElement|null
     */
    public function getLastElementChild(): ?DOMElement;
}
