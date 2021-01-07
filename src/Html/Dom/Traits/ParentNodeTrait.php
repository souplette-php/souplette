<?php declare(strict_types=1);

namespace Souplette\Html\Dom\Traits;

use DOMElement;
use DOMNode;
use Souplette\Html\Dom\DomIdioms;

trait ParentNodeTrait
{
    /**
     * @return DOMElement[]
     */
    public function getChildren(): array
    {
        $children = [];
        foreach ($this->childNodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                $children[] = $node;
            }
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

    public function querySelector(string $selector): ?DOMElement
    {
        throw new \LogicException('Not implemented');
    }

    public function querySelectorAll(string $selector): iterable
    {
        throw new \LogicException('Not implemented');
    }
}
