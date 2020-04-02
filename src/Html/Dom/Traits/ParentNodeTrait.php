<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Traits;

use DOMElement;
use JoliPotage\Html\Dom\DomIdioms;

trait ParentNodeTrait
{
    public function prepend(...$nodes): void
    {
        // 1. Let node be the result of converting nodes into a node given nodes and this’s node document.
        $doc = DomIdioms::getOwnerDocument($this);
        $node = DomIdioms::convertNodesIntoNode($doc, $nodes);
        // 2. Pre-insert node into this before this’s first child.
        $this->insertBefore($node, $this->firstChild);
    }

    public function append(...$nodes): void
    {
        $doc = DomIdioms::getOwnerDocument($this);
        // 1. Let node be the result of converting nodes into a node given nodes and this’s node document.
        $node = DomIdioms::convertNodesIntoNode($doc, $nodes);
        // 2. Append node to this.
        $this->appendChild($node);
    }

    public function querySelector(string $selector): ?DOMElement
    {
        // TODO: Implement querySelector() method.
    }

    public function querySelectorAll(string $selector)
    {
        // TODO: Implement querySelectorAll() method.
    }

    public function getChildren()
    {
        $children = [];
        foreach ($this->childNodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                $children[] = $node;
            }
        }
        return $children;
    }

    public function getFirstElementChild(): ?DOMElement
    {
        foreach ($this->childNodes as $node) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                return $node;
            }
        }
        return null;
    }

    public function getLastElementChild(): ?DOMElement
    {
        $node = $this->lastChild;
        while ($node && $node->nodeType !== XML_ELEMENT_NODE) {
            $node = $node->previousSibling;
        }
        return $node;
    }

}
