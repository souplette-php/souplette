<?php declare(strict_types=1);

namespace Souplette\Dom\Traits;

use DOMNode;
use Souplette\Dom\Internal\DomIdioms;

trait ChildNodeTrait
{
    /**
     * @todo remove when https://bugs.php.net/bug.php?id=80602 is fixed
     * @param DOMNode|string $nodes
     */
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

    /**
     * @todo remove when https://bugs.php.net/bug.php?id=80602 is fixed
     * @param DOMNode|string $nodes
     */
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

    /**
     * @param DOMNode|string ...$nodes
     */
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
        } else {
            // 6. Otherwise, pre-insert node into parent before viableNextSibling.
            $parent->insertBefore($node, $viableNextSibling);
        }
    }

    /**
     * @todo remove this when  https://bugs.php.net/bug.php?id=80600 is closed.
     */
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
