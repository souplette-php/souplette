<?php declare(strict_types=1);


namespace JoliPotage\Html\Dom\Traits;


use JoliPotage\Html\Dom\DomIdioms;

trait ChildNodeTrait
{
    public function before(...$nodes): void
    {
        // 1. Let parent be this’s parent.
        $parent = $this->parentNode;
        // 2. If parent is null, then return.
        if (!$parent) {
            return;
        }
        // 3. Let viablePreviousSibling be this’s first preceding sibling not in nodes, and null otherwise.
        $viablePreviousSibling = DomIdioms::findViablePreviousSibling($this, $nodes);
        // 4. Let node be the result of converting nodes into a node, given nodes and this’s node document.
        $node = DomIdioms::convertNodesIntoNode($this->ownerDocument, $nodes);
        // 5. If viablePreviousSibling is null, set it to parent’s first child, and to viablePreviousSibling’s next sibling otherwise.
        if ($viablePreviousSibling === null) {
            $viablePreviousSibling = $parent->firstChild;
        } else {
            $viablePreviousSibling = $viablePreviousSibling->nextSibling;
        }
        // 6. Pre-insert node into parent before viablePreviousSibling.
        $parent->insertBefore($node, $viablePreviousSibling);
    }

    public function after(...$nodes): void
    {
        // 1. Let parent be this’s parent.
        $parent = $this->parentNode;
        // 2. If parent is null, then return.
        if (!$parent) {
            return;
        }
        // 3. Let viableNextSibling be this’s first following sibling not in nodes, and null otherwise.
        $viableNextSibling = DomIdioms::findViableNextSibling($this, $nodes);
        // 4. Let node be the result of converting nodes into a node, given nodes and this’s node document.
        $node = DomIdioms::convertNodesIntoNode($this->ownerDocument, $nodes);
        // 5. Pre-insert node into parent before viableNextSibling
        $parent->insertBefore($node, $viableNextSibling);
    }

    public function replaceWith(...$nodes): void
    {
        // 1. Let parent be this’s parent.
        $parent = $this->parentNode;
        // 2. If parent is null, then return.
        if (!$parent) {
            return;
        }
        // 3. Let viableNextSibling be this’s first following sibling not in nodes, and null otherwise.
        $viableNextSibling = DomIdioms::findViableNextSibling($this, $nodes);
        // 4. Let node be the result of converting nodes into a node, given nodes and this’s node document.
        $node = DomIdioms::convertNodesIntoNode($this->ownerDocument, $nodes);
        // 5. If this’s parent is parent, replace this with node within parent.
        //    This could have been inserted into node.
        if ($this->parentNode === $parent) {
            $this->parentNode->replaceChild($node, $this);
        }
        // 6. Otherwise, pre-insert node into parent before viableNextSibling.
        $parent->insertBefore($node, $viableNextSibling);
    }

    public function remove(): void
    {
        // 1. If this’s parent is null, then return.
        if (!$this->parentNode) {
            return;
        }
        // 2. Remove this.
        $this->parentNode->removeChild($this);
    }
}
