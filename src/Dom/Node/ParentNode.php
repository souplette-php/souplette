<?php declare(strict_types=1);

namespace Souplette\Dom\Node;

use Souplette\Css\Selectors\SelectorQuery;
use Souplette\Dom\Api\ParentNodeInterface;
use Souplette\Dom\Exception\DomException;
use Souplette\Dom\Exception\HierarchyRequestError;
use Souplette\Dom\Exception\NotFoundError;

/**
 * Extended by Document, DocumentFragment & Element
 *
 * @property-read Element[] $children
 * @property-read ?Element $firstElementChild
 * @property-read ?Element $lastElementChild
 * @property-read int $childElementCount
 */
abstract class ParentNode extends Node implements ParentNodeInterface
{
    public function __get(string $prop)
    {
        return match ($prop) {
            'textContent' => $this->getTextContent(),
            'children' => $this->getChildren(),
            'firstElementChild' => $this->getFirstElementChild(),
            'lastElementChild' => $this->getLastElementChild(),
            'childElementCount' => $this->getChildElementCount(),
            default => parent::__get($prop),
        };
    }

    public function __set(string $prop, mixed $value)
    {
        match ($prop) {
            'textContent' => $this->setTextContent($value),
            default => parent::__set($prop, $value),
        };
    }

    public function hasChildNodes(): bool
    {
        return $this->first !== null;
    }

    public function getChildNodes(): array
    {
        $nodes = [];
        for ($child = $this->first; $child; $child = $child->next) {
            $nodes[] = $child;
        }
        return $nodes;
    }

    /**
     * @return Element[]
     */
    public function getChildren(): array
    {
        $children = [];
        for ($child = $this->first; $child; $child = $child->next) {
            if ($child->nodeType === Node::ELEMENT_NODE) {
                $children[] = $child;
            }
        }
        return $children;
    }

    public function getFirstElementChild(): ?Element
    {
        for ($child = $this->first; $child; $child = $child->next) {
            if ($child->nodeType === Node::ELEMENT_NODE) {
                return $child;
            }
        }
        return null;
    }

    public function getLastElementChild(): ?Element
    {
        for ($child = $this->last; $child; $child = $child->prev) {
            if ($child->nodeType === Node::ELEMENT_NODE) {
                return $child;
            }
        }
        return null;
    }

    public function getChildElementCount(): int
    {
        $count = 0;
        for ($current = $this->first; $current; $current = $current->next) {
            if ($current->nodeType ===  self::ELEMENT_NODE) $count++;
        }
        return $count;
    }

    /**
     * @throws DomException
     */
    public function appendChild(Node $node): Node
    {
        return $this->preInsertNodeBeforeChild($node, null);
    }

    /**
     * @throws DomException
     */
    public function insertBefore(Node $node, ?Node $child = null): Node
    {
        return $this->preInsertNodeBeforeChild($node, $child);
    }

    /**
     * @throws NotFoundError
     */
    public function removeChild(Node $child): Node
    {
        // https://dom.spec.whatwg.org/#dom-node-removechild
        // The removeChild(child) method steps are to return the result of pre-removing child from this.
        return $this->preRemoveChild($child);
    }

    /**
     * @throws NotFoundError|HierarchyRequestError
     */
    public function replaceChild(Node $node, Node $child): Node
    {
        // https://dom.spec.whatwg.org/#dom-node-replacechild
        // The replaceChild(node, child) method steps are to return the result of replacing child with node within this.
        return $this->replaceChildWithNode($child, $node);
    }

    /**
     * @throws DomException
     */
    public function prepend(Node|string ...$nodes): void
    {
        // 1. Let node be the result of converting nodes into a node given nodes and this’s node document.
        $node = $this->convertNodesIntoNode($nodes);
        // 2. Pre-insert node into this before this’s first child.
        $this->preInsertNodeBeforeChild($node, $this->first);
    }

    /**
     * @throws DomException
     */
    public function append(Node|string ...$nodes): void
    {
        // 1. Let node be the result of converting nodes into a node given nodes and this’s node document.
        $node = $this->convertNodesIntoNode($nodes);
        // 2. Append node to this.
        $this->preInsertNodeBeforeChild($node, null);
    }

    /**
     * @throws NotFoundError
     * @throws HierarchyRequestError
     * @throws DomException
     */
    public function replaceChildren(Node|string ...$nodes): void
    {
        // 1. Let node be the result of converting nodes into a node given nodes and this’s node document.
        $node = $this->convertNodesIntoNode($nodes);
        // 2. Ensure pre-insertion validity of node into this before null.
        $this->ensurePreInsertionValidity($node, null);
        // 3. Replace all with node within this.
        $this->replaceAllWithNode($node);
    }

    public function getTextContent(): ?string
    {
        $text = '';
        foreach ($this->descendants() as $node) {
            if ($node->nodeType === Node::TEXT_NODE || $node->nodeType === Node::CDATA_SECTION_NODE) {
                $text .= $node->value;
            }
        }
        return $text;
    }

    public function setTextContent(string $value): void
    {
        if (!$value) {
            $this->replaceAllWithNode(null);
            return;
        }
        $node = new Text($value);
        $node->document = $this->getDocumentNode();
        $this->replaceAllWithNode($node);
    }

    public function isEqualNode(?Node $otherNode): bool
    {
        if (!$otherNode) return false;
        if ($otherNode === $this) return true;
        if ($otherNode->nodeType !== $this->nodeType) return false;
        return $this->areChildrenEqual($otherNode);
    }


    public function querySelector(string $selector): ?Element
    {
        return SelectorQuery::first($this, $selector);
    }

    public function querySelectorAll(string $selector): array
    {
        return SelectorQuery::all($this, $selector);
    }

    protected function areChildrenEqual(?Node $other): bool
    {
        for (
            $child = $this->first, $otherChild = $other->first;
            $child && $otherChild;
            $child = $child->next, $otherChild = $otherChild->next
        ) {
            if (!$child->isEqualNode($otherChild)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return iterable<Node>
     */
    protected function descendants(): iterable
    {
        $node = $this->first;
        while ($node) {
            yield $node;
            if ($node->first) {
                $node = $node->first;
                continue;
            }
            while ($node) {
                if ($node === $this) {
                    break 2;
                }
                if ($node->next) {
                    $node = $node->next;
                    continue 2;
                }
                $node = $node->parent;
            }
        }
    }

    // ==============================================================
    // Mutation algorithms
    // ==============================================================

    /**
     * https://dom.spec.whatwg.org/#concept-node-insert
     */
    protected function insertNodeBeforeChild(Node $node, ?Node $child): void
    {
        // 1. Let nodes be node’s children, if node is a DocumentFragment node; otherwise « node ».
        $isFragment = $node->nodeType === self::DOCUMENT_FRAGMENT_NODE;
        $nodes = $isFragment ? $node->collectChildNodes() : [$node];
        // 2. Let count be nodes’s size.
        // 3. If count is 0, then return.
        if (!$nodes) return;
        // 4. If node is a DocumentFragment node, then:
        if ($isFragment) {
            // 1. Remove its children with the suppress observers flag set.
            // 2. Queue a tree mutation record for node with « », nodes, null, and null.
        }
        // skipping step 5 (live ranges)
        // skipping step 6 (mutation records)
        // 7. For each node in nodes, in tree order:
        foreach ($nodes as $current) {
            // 1. Adopt node into parent’s node document.
            $this->adopt($current);
            // 2. If child is null, then append node to parent’s children.
            $current->unlink();
            if (!$child) {
                $this->uncheckedAppendChild($current);
            } else {
                // 3. Otherwise, insert node into parent’s children before child’s index.
                $this->uncheckedInsertBefore($current, $child);
            }
        }
    }

    /**
     * https://dom.spec.whatwg.org/#concept-node-pre-remove
     */
    protected function preRemoveChild(Node $child): Node
    {
        // 1. If child’s parent is not parent, then throw a "NotFoundError" DOMException.
        if ($child->parent !== $this) {
            throw new NotFoundError();
        }
        // 2. Remove child.
        $this->removeNode($child);
        // 3. Return child.
        return $child;
    }

    /**
     * https://dom.spec.whatwg.org/#concept-node-remove
     */
    protected function removeNode(Node $node)
    {
        // 1. Let parent be node’s parent
        $parent = $node->parent;
        // 2. Assert: parent is non-null.
        assert($parent !== null);
        // 3. Let index be node’s index.
        $node->unlink();
        // blah blah live ranges...

        // 8. For each NodeIterator object iterator whose root’s node document is node’s node document,
        // run the NodeIterator pre-removing steps given node and iterator.

        // blah blah shadow dom, mutation records, etc...
    }

    /**
     * https://dom.spec.whatwg.org/#concept-node-replace-all
     */
    protected function replaceAllWithNode(?Node $node)
    {
        // 1. Let removedNodes be parent’s children.
        // 2. Let addedNodes be the empty set.
        // 3. If node is a DocumentFragment node, then set addedNodes to node’s children.
        // 4. Otherwise, if node is non-null, set addedNodes to « node ».
        // 5. Remove all parent’s children, in tree order, with the suppress observers flag set.
        $current = $this->first;
        while ($current) {
            $next = $current->next;
            $this->removeNode($current);
            $current = $next;
        }
        // 6. If node is non-null, then insert node into parent before null with the suppress observers flag set.
        if ($node) {
            $this->insertNodeBeforeChild($node, null);
        }
        // 7. If either addedNodes or removedNodes is not empty,
        // then queue a tree mutation record for parent with addedNodes, removedNodes, null, and null.
    }

    protected const VALID_CHILD_TYPES = [
        Node::DOCUMENT_FRAGMENT_NODE => true,
        Node::ELEMENT_NODE => true,
        Node::TEXT_NODE => true,
        Node::COMMENT_NODE => true,
        Node::CDATA_SECTION_NODE => true,
        Node::PROCESSING_INSTRUCTION_NODE => true,
    ];

    /**
     * https://dom.spec.whatwg.org/#concept-node-ensure-pre-insertion-validity
     * @throws HierarchyRequestError
     * @throws NotFoundError
     */
    protected function ensurePreInsertionValidity(Node $node, ?Node $child): void
    {
        // 1. If parent is not a Document, DocumentFragment, or Element node,
        // then throw a "HierarchyRequestError" DOMException.
        // NOTE: this step is ensured by the class hierarchy
        // 2. If node is a host-including inclusive ancestor of parent, then throw a "HierarchyRequestError" DOMException.
        for ($current = $this; $current; $current = $current->parent) {
            if ($current === $node) {
                throw new HierarchyRequestError('The new child element contains the parent.');
            }
        }
        // 3. If child is non-null and its parent is not parent, then throw a "NotFoundError" DOMException.
        if ($child && $child->parent !== $this) {
            throw new NotFoundError(
                'The node before which the new node is to be inserted is not a child of this node.'
            );
        }
        // 4. If node is not a DocumentFragment, DocumentType, Element, or CharacterData node,
        // then throw a "HierarchyRequestError" DOMException.
        if (!isset(static::VALID_CHILD_TYPES[$node->nodeType])) {
            throw new HierarchyRequestError(sprintf(
                'Nodes of type `%s` may not be inserted inside nodes of type `%s`',
                $node->getDebugType(),
                $this->getDebugType(),
            ));
        }
        // 5. If either node is a Text node and parent is a document, or node is a doctype and parent is not a document,
        // then throw a "HierarchyRequestError" DOMException.
        // NOTE: this is handled by he VALID_CHILD_TYPE constant overridden by the Document class

        // 6. If parent is a document, and any of the statements below,
        // switched on the interface node implements, are true,
        // then throw a "HierarchyRequestError" DOMException.
        // NOTE: this step is ensured by the Document class
    }

    /**
     * Handles validation steps for https://dom.spec.whatwg.org/#concept-node-replace
     *
     * @throws NotFoundError
     * @throws HierarchyRequestError
     */
    protected function ensureReplacementValidity(Node $child, Node $node): void
    {
        $this->ensurePreInsertionValidity($node, $child);
    }

    /**
     * @return Node[]
     */
    protected function collectChildNodes(): array
    {
        $children = [];
        for ($child = $this->first; $child; $child = $child->next) {
            $children[] = $child;
        }
        return $children;
    }

    protected function hasChildOfType(int $type): bool
    {
        for ($child = $this->first; $child; $child = $child->next) {
            if ($child->nodeType === $type) return true;
        }
        return false;
    }

    protected function hasChildOfTypeThatIsNotChild(int $type, Node $child): bool
    {
        for ($node = $this->first; $node; $node = $node->next) {
            if ($node->nodeType === $type && $node !== $child) return true;
        }
        return false;
    }
}
