<?php declare(strict_types=1);

namespace Souplette\Dom\Traits;

use DOMElement;
use DOMNode;
use Souplette\Css\Selectors\SelectorQuery;
use Souplette\Dom\Element;
use Souplette\Dom\Internal\DomIdioms;

trait ParentNodeTrait
{
    /**
     * @return DOMElement[]
     */
    public function getChildren(): array
    {
        $children = [];
        $child = $this->firstElementChild;
        while ($child) {
            $children[] = $child;
            $child = $child->nextElementSibling;
        }
        return $children;
    }

    /**
     * @param DOMNode|string ...$nodes
     */
    public function prepend(...$nodes): void
    {
        // 1. Let node be the result of converting nodes into a node given nodes and this’s node document.
        $doc = DomIdioms::getOwnerDocument($this);
        $node = DomIdioms::convertNodesIntoNode($doc, $nodes);
        // 2. Pre-insert node into this before this’s first child.
        $this->insertBefore($node, $this->firstChild);
    }

    /**
     * @param DOMNode|string ...$nodes
     */
    public function append(...$nodes): void
    {
        $doc = DomIdioms::getOwnerDocument($this);
        // 1. Let node be the result of converting nodes into a node given nodes and this’s node document.
        $node = DomIdioms::convertNodesIntoNode($doc, $nodes);
        // 2. Append node to this.
        $this->appendChild($node);
    }

    /**
     * @param DOMNode|string ...$nodes
     */
    public function replaceChildren(...$nodes): void
    {
        $doc = DomIdioms::getOwnerDocument($this);
        // 1. Let node be the result of converting nodes into a node given nodes and this’s node document.
        $node = DomIdioms::convertNodesIntoNode($doc, $nodes);
        // 2. Ensure pre-insertion validity of node into this before null.
        // 3. Replace all with node within this.
        DomIdioms::replaceAllWithNodeWithinParent($node, $this);
    }

    public function querySelector(string $selector): ?Element
    {
        return SelectorQuery::queryFirst($this, $selector);
    }

    public function querySelectorAll(string $selector): array
    {
        return SelectorQuery::queryAll($this, $selector);
    }
}
