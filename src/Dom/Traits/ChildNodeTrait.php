<?php declare(strict_types=1);

namespace Souplette\Dom\Traits;

use Souplette\Dom\Exception\DomException;
use Souplette\Dom\Exception\NotFoundError;
use Souplette\Dom\Internal\Idioms;
use Souplette\Dom\Node;

/**
 * Used by DocumentType, Element and CharacterData
 */
trait ChildNodeTrait
{
    /**
     * @throws DomException
     */
    public function before(Node|string ...$nodes): void
    {
        // 1. Let parent be this’s parent.
        $parent = $this->_parent;
        // 2. If parent is null, then return.
        if (!$parent) {
            return;
        }
        // 3. Let viablePreviousSibling be this’s first preceding sibling not in nodes, and null otherwise.
        $viablePreviousSibling = $this->findViablePreviousSibling($nodes);
        // 4. Let node be the result of converting nodes into a node, given nodes and this’s node document.
        $node = Idioms::convertNodesIntoNode($this->getDocumentNode(), $nodes);
        // 5. If viablePreviousSibling is null, set it to parent’s first child, and to viablePreviousSibling’s next sibling otherwise.
        if ($viablePreviousSibling === null) {
            $viablePreviousSibling = $parent->_first;
        } else {
            $viablePreviousSibling = $viablePreviousSibling->_next;
        }
        // 6. Pre-insert node into parent before viablePreviousSibling.
        //$parent->preInsertNodeBeforeChild($node, $viablePreviousSibling);
        $parent->insertBefore($node, $viablePreviousSibling);
    }

    /**
     * @throws DomException
     */
    public function after(Node|string ...$nodes): void
    {
        // 1. Let parent be this’s parent.
        $parent = $this->_parent;
        // 2. If parent is null, then return.
        if (!$parent) {
            return;
        }
        // 3. Let viableNextSibling be this’s first following sibling not in nodes, and null otherwise.
        $viableNextSibling = $this->findViableNextSibling($nodes);
        // 4. Let node be the result of converting nodes into a node, given nodes and this’s node document.
        $node = Idioms::convertNodesIntoNode($this->getDocumentNode(), $nodes);
        // 5. Pre-insert node into parent before viableNextSibling
        //$parent->preInsertNodeBeforeChild($node, $viableNextSibling);
        $parent->insertBefore($node, $viableNextSibling);
    }

    /**
     * @throws DomException
     */
    public function replaceWith(Node|string ...$nodes): void
    {
        // 1. Let parent be this’s parent.
        $parent = $this->_parent;
        // 2. If parent is null, then return.
        if (!$parent) {
            return;
        }
        // 3. Let viableNextSibling be this’s first following sibling not in nodes, and null otherwise.
        $viableNextSibling = $this->findViableNextSibling($nodes);
        // 4. Let node be the result of converting nodes into a node, given nodes and this’s node document.
        $node = Idioms::convertNodesIntoNode($this->getDocumentNode(), $nodes);
        // 5. If this’s parent is parent, replace this with node within parent.
        //    This could have been inserted into node.
        if ($this->_parent === $parent) {
            //$parent->replaceChildWithNode($this, $node);
            $parent->replaceChild($node, $this);
        } else {
            // 6. Otherwise, pre-insert node into parent before viableNextSibling.
            //$parent->preInsertNodeBeforeChild($node, $viableNextSibling);
            $parent->insertBefore($node, $viableNextSibling);
        }
    }

    /**
     * @throws NotFoundError
     */
    public function remove(): void
    {
        // 1. If this’s parent is null, then return.
        if (!$this->_parent) {
            return;
        }
        // 2. Remove this.
        $this->_parent->removeChild($this);
    }

    /**
     * @param array<Node|string> $nodes
     * @return Node|null
     */
    private function findViableNextSibling(array $nodes): ?Node
    {
        for ($sibling = $this->_next; $sibling; $sibling = $sibling->_next) {
            if (!\in_array($sibling, $nodes, true)) {
                return $sibling;
            }
        }
        return null;
    }

    /**
     * @param array<Node|string> $nodes
     * @return Node|null
     */
    private function findViablePreviousSibling(array $nodes): ?Node
    {
        for ($sibling = $this->_prev; $sibling; $sibling = $sibling->_prev) {
            if (!\in_array($sibling, $nodes, true)) {
                return $sibling;
            }
        }
        return null;
    }
}
