<?php declare(strict_types=1);

namespace Souplette\Dom\Node;

use Souplette\Dom\Exception\DomException;
use Souplette\Dom\Exception\HierarchyRequestError;
use Souplette\Dom\Exception\NotFoundError;

/**
 * @property-read ?string $nodeValue
 * @property ?string $textContent
 *
 * @property-read ?Document $ownerDocument
 * @property-read ?Node $parentNode
 * @property-read ?Node $parentElement
 * @property-read ?Node $firstChild
 * @property-read ?Node $lastChild
 * @property-read ?Node $nextSibling
 * @property-read ?Node $previousSibling
 */
abstract class Node
{
    const ELEMENT_NODE = 1;
    const ATTRIBUTE_NODE = 2;
    const TEXT_NODE = 3;
    const CDATA_SECTION_NODE = 4;
    const ENTITY_REFERENCE_NODE = 5; // historical
    const ENTITY_NODE = 6; // historical
    const PROCESSING_INSTRUCTION_NODE = 7;
    const COMMENT_NODE = 8;
    const DOCUMENT_NODE = 9;
    const DOCUMENT_TYPE_NODE = 10;
    const DOCUMENT_FRAGMENT_NODE = 11;
    const NOTATION_NODE = 12; // historical
    const HTML_DOCUMENT_NODE = 13;

    /**
     * @see https://dom.spec.whatwg.org/#dom-node-comparedocumentposition
     */
    const DOCUMENT_POSITION_DISCONNECTED = 0x01;
    const DOCUMENT_POSITION_PRECEDING = 0x02;
    const DOCUMENT_POSITION_FOLLOWING = 0x04;
    const DOCUMENT_POSITION_CONTAINS = 0x08;
    const DOCUMENT_POSITION_CONTAINED_BY = 0x10;
    const DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC = 0x20;

    public readonly int $nodeType;
    public readonly string $nodeName;

    protected ?string $value = null;
    protected ?Document $document = null;
    protected ?Node $parent = null;
    protected ?Node $next = null;
    protected ?Node $prev = null;
    protected ?Node $first = null;
    protected ?Node $last = null;

    public function __get(string $prop)
    {
        return match ($prop) {
            'nodeValue' => null,
            'ownerDocument' => $this->document,
            'parentNode' => $this->parent,
            'firstChild' => $this->first,
            'lastChild' => $this->last,
            'nextSibling' => $this->next,
            'previousSibling' => $this->prev,
            'parentElement' => $this->getParentElement(),
            default => throw new \RuntimeException(sprintf(
                'Undefined property %s::$%s',
                $this::class,
                $prop,
            ))
        };
    }

    public function __set(string $prop, mixed $value)
    {
        match ($prop) {
            'nodeValue' => null,
            default => throw new \RuntimeException(sprintf(
                'Undefined property %s::$%s',
                $this::class,
                $prop,
            ))
        };
    }

    public function isSameNode(?Node $otherNode): bool
    {
        return $this === $otherNode;
    }

    abstract public function isEqualNode(?Node $otherNode): bool;

    /**
     * The getRootNode(options) method steps are to return this’s shadow-including root if options["composed"] is true;
     * otherwise this’s root.
     *
     * https://dom.spec.whatwg.org/#dom-node-getrootnode
     */
    public function getRootNode(array $options = []): Node
    {
        $root = $this->parent;
        while ($root) $root = $root->parent;
        return $root;
    }

    public function hasChildNodes(): bool
    {
        return false;
    }

    /**
     * @throws DomException
     */
    public function appendChild(Node $node): Node
    {
        throw new HierarchyRequestError();
    }

    /**
     * @throws DomException
     */
    public function insertBefore(Node $node, ?Node $child = null): Node
    {
        throw new HierarchyRequestError();
    }

    /**
     * @throws DomException
     */
    public function removeChild(Node $child): Node
    {
        throw new HierarchyRequestError();
    }

    /**
     * @throws HierarchyRequestError
     */
    public function replaceChild(Node $node, Node $child): Node
    {
        throw new HierarchyRequestError();
    }

    public function getParentElement(): ?Element
    {
        for ($node = $this->parent; $node; $node = $node->parent) {
            if ($node->nodeType === self::ELEMENT_NODE) {
                return $node;
            }
        }
        return null;
    }

    public function getChildElementCount(): int
    {
        return 0;
    }

    // ==============================================================
    // Mutation algorithms
    // ==============================================================

    /**
     * https://dom.spec.whatwg.org/#concept-node-pre-insert
     * @throws DomException
     */
    protected function preInsertNodeBeforeChild(Node $node, ?Node $child): Node
    {
        // To pre-insert a node into a parent before a child, run these steps:
        // 1. Ensure pre-insertion validity of node into parent before child.
        $this->ensurePreInsertionValidity($node, $child);
        // 2. Let referenceChild be child.
        $referenceChild = $child;
        // 3. If referenceChild is node, then set referenceChild to node’s next sibling.
        if ($referenceChild === $node) {
            $referenceChild = $node->next;
        }
        // 4. Insert node into parent before referenceChild.
        $this->insertNodeBeforeChild($node, $referenceChild);
        // 5. Return node.
        return $node;
    }

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

    protected function adopt(Node $node): void
    {
        if ($node->document === $this->document) {
            return;
        }
        $node->document = $this->document;
        for ($child = $node->first; $child; $child = $child->next) {
            $this->adopt($node);
        }
    }

    protected function uncheckedAppendChild(Node $node): void
    {
        $node->parent = $this;
        $node->next = $node->prev = null;
        if ($this->first === null) {
            $this->first = $node;
            $this->last = $node;
        } else {
            $last = $this->last;
            $last->next = $node;
            $node->prev = $last;
            $this->last = $node;
        }
    }

    protected function uncheckedInsertBefore(Node $node, Node $child): void
    {
        $node->parent = $this;
        $node->next = $child;
        $node->prev = $child->prev;
        $child->prev = $node;
        if ($node->prev) {
            $node->prev->next = $node;
        }
        if ($this->first === $child) {
            $this->first = $node;
        }
    }

    protected function unlink(): void
    {
        if ($parent = $this->parent) {
            if ($parent->first === $this) {
                $parent->first = $this->next;
            }
            if ($parent->last === $this) {
                $parent->last = $this->prev;
            }
            $this->parent = null;
        }
        if ($this->next) {
            $this->next->prev = $this->prev;
        }
        if ($this->prev) {
            $this->prev->next = $this->next;
        }
        $this->next = $this->prev = null;
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
     * https://dom.spec.whatwg.org/#concept-node-replace
     *
     * @throws HierarchyRequestError|NotFoundError
     */
    protected function replaceChildWithNode(Node $child, Node $node): Node
    {
        // 1. If parent is not a Document, DocumentFragment, or Element node,
        // then throw a "HierarchyRequestError" DOMException.
        match ($this->nodeType) {
            self::DOCUMENT_NODE, self::DOCUMENT_FRAGMENT_NODE, self::ELEMENT_NODE => null,
            default => throw new HierarchyRequestError(),
        };
        // 2. If node is a host-including inclusive ancestor of parent,
        // then throw a "HierarchyRequestError" DOMException.
        for ($current = $this; $current; $current = $current->parent) {
            if ($current === $node) {
                throw new HierarchyRequestError();
            }
        }
        // 3. If child’s parent is not parent, then throw a "NotFoundError" DOMException.
        if ($child->parent !== $this) {
            throw new NotFoundError();
        }
        // 4. If node is not a DocumentFragment, DocumentType, Element, or CharacterData node,
        // then throw a "HierarchyRequestError" DOMException.
        match ($this->nodeType) {
            self::DOCUMENT_FRAGMENT_NODE, self::DOCUMENT_TYPE_NODE, self::ELEMENT_NODE,
            self::TEXT_NODE, self::COMMENT_NODE, self::CDATA_SECTION_NODE, self::PROCESSING_INSTRUCTION_NODE
            => null,
            default => throw new HierarchyRequestError(),
        };
        // 5. If either node is a Text node and parent is a document,
        // or node is a doctype and parent is not a document,
        // then throw a "HierarchyRequestError" DOMException.
        if (
            ($node->nodeType === self::TEXT_NODE && $this->nodeType === self::DOCUMENT_NODE)
            || ($node->nodeType === self::DOCUMENT_TYPE_NODE && $this->nodeType !== self::DOCUMENT_NODE)
        ) {
            throw new HierarchyRequestError();
        }
        // 6. If parent is a document, and any of the statements below,
        // switched on the interface node implements, are true,
        // then throw a "HierarchyRequestError" DOMException.
        if ($this->nodeType === self::DOCUMENT_NODE) {
            switch ($node->nodeType) {
                case self::DOCUMENT_FRAGMENT_NODE:
                    $nodeChildElementCount = $node->getChildElementCount();
                    if ($nodeChildElementCount > 1
                        || $node->hasChildOfType(self::TEXT_NODE)
                        || ($nodeChildElementCount === 1 && (
                                $this->hasChildOfTypeThatIsNotChild(self::ELEMENT_NODE, $child)
                                || $child->hasFollowingSiblingOfType(self::DOCUMENT_TYPE_NODE)
                            ))
                    ) {
                        throw new HierarchyRequestError();
                    }
                    break;
                case self::ELEMENT_NODE:
                    if ($this->hasChildOfTypeThatIsNotChild(self::ELEMENT_NODE, $child)
                        || $child->hasFollowingSiblingOfType(self::DOCUMENT_TYPE_NODE)
                    ) {
                        throw new HierarchyRequestError();
                    }
                    break;
                case self::DOCUMENT_TYPE_NODE:
                    if ($this->hasChildOfTypeThatIsNotChild(self::DOCUMENT_TYPE_NODE, $child)
                        || $child->hasPrecedingSiblingOfType(self::ELEMENT_NODE)
                    ) {
                        throw new HierarchyRequestError();
                    }
                    break;
            }
        }
        // 7. Let referenceChild be child’s next sibling.
        $referenceChild = $child->next;
        // 8. If referenceChild is node, then set referenceChild to node’s next sibling.
        if ($referenceChild === $node) $referenceChild = $node->next;
        // 11. If child’s parent is non-null, then:
        if ($child->parent) {
            // 1. Set removedNodes to « child ».
            // 2. Remove child with the suppress observers flag set.
            $this->removeNode($child);
        }
        // 12. Let nodes be node’s children if node is a DocumentFragment node; otherwise « node ».
        // 13. Insert node into parent before referenceChild with the suppress observers flag set.
        $this->insertNodeBeforeChild($node, $referenceChild);
        // 14. Queue a tree mutation record for parent with nodes, removedNodes, previousSibling, and referenceChild.
        // 15. Return child.
        return $child;
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

    /**
     * https://dom.spec.whatwg.org/#concept-node-ensure-pre-insertion-validity
     * @throws HierarchyRequestError
     * @throws NotFoundError
     */
    protected function ensurePreInsertionValidity(Node $node, ?Node $child)
    {
        // 1. If parent is not a Document, DocumentFragment, or Element node, then throw a "HierarchyRequestError" DOMException.
        match ($this->nodeType) {
            self::DOCUMENT_NODE, self::DOCUMENT_FRAGMENT_NODE, self::ELEMENT_NODE => null,
            default => throw new HierarchyRequestError(),
        };
        // 2. If node is a host-including inclusive ancestor of parent, then throw a "HierarchyRequestError" DOMException.
        for ($current = $this; $current; $current = $current->parent) {
            if ($current === $node) {
                throw new HierarchyRequestError();
            }
        }
        // 3. If child is non-null and its parent is not parent, then throw a "NotFoundError" DOMException.
        if ($child && $child->parent !== $this) {
            throw new NotFoundError();
        }
        // 4. If node is not a DocumentFragment, DocumentType, Element, or CharacterData node, then throw a "HierarchyRequestError" DOMException.
        match ($node->nodeType) {
            self::DOCUMENT_FRAGMENT_NODE, self::DOCUMENT_TYPE_NODE, self::ELEMENT_NODE,
            self::TEXT_NODE, self::COMMENT_NODE, self::CDATA_SECTION_NODE, self::PROCESSING_INSTRUCTION_NODE
                => null,
            default => throw new HierarchyRequestError(),
        };
        // 5. If either node is a Text node and parent is a document, or node is a doctype and parent is not a document,
        // then throw a "HierarchyRequestError" DOMException.
        if (
            ($node->nodeType === self::TEXT_NODE && $this->nodeType === self::DOCUMENT_NODE)
            || ($node->nodeType === self::DOCUMENT_TYPE_NODE && $this->nodeType !== self::DOCUMENT_NODE)
        ) {
            throw new HierarchyRequestError();
        }
        // 6. If parent is a document, and any of the statements below,
        // switched on the interface node implements, are true,
        // then throw a "HierarchyRequestError" DOMException.
        if ($this->nodeType === self::DOCUMENT_NODE) {
            switch ($node->nodeType) {
                case self::DOCUMENT_FRAGMENT_NODE:
                    // If node has more than one element child or has a Text node child.
                    $nodeChildElementCount = $node->getChildElementCount();
                    if ($nodeChildElementCount > 1 || $node->hasChildOfType(self::TEXT_NODE)) {
                        throw new HierarchyRequestError();
                    }
                    // Otherwise, if node has one element child and either parent has an element child,
                    //  child is a doctype, or child is non-null and a doctype is following child.
                    if ($nodeChildElementCount === 1
                        && (
                            $this->getChildElementCount() > 0
                            || ($child && $child->nodeType === self::DOCUMENT_TYPE_NODE)
                            || ($child && $child->hasFollowingSiblingOfType(self::DOCUMENT_TYPE_NODE))
                        )
                    ) {
                        throw new HierarchyRequestError();
                    }
                    break;
                case self::ELEMENT_NODE:
                    // parent has an element child, child is a doctype, or child is non-null and a doctype is following child.
                    if ($this->getChildElementCount() > 0
                        || ($child && $child->nodeType === self::DOCUMENT_TYPE_NODE)
                        || ($child && $child->hasFollowingSiblingOfType(self::DOCUMENT_TYPE_NODE))
                    ) {
                        throw new HierarchyRequestError();
                    }
                    break;
                case self::DOCUMENT_TYPE_NODE:
                    // parent has a doctype child, child is non-null and an element is preceding child,
                    // or child is null and parent has an element child.
                    if ($this->hasChildOfType(self::DOCUMENT_TYPE_NODE)
                        || ($child && $child->hasPrecedingSiblingOfType(self::ELEMENT_NODE))
                        || (!$child && $this->hasChildOfType(self::ELEMENT_NODE))
                    ) {
                        throw new HierarchyRequestError();
                    }
                    break;
            }
        }
    }

    /**
     * @throws DomException
     */
    protected function convertNodesIntoNode(array $nodes): Node
    {
        $doc = $this->getDocument();
        // 1. Let node be null.
        // 2. Replace each string in nodes with a new Text node whose data is the string and node document is document.
        foreach ($nodes as $i => $node) {
            if (\is_string($node)) {
                $node = new Text($node);
                $node->document = $doc;
                $nodes[$i] = $node;
            }
        }
        // 3. If nodes contains one node, then set node to nodes[0].
        if (\count($nodes) === 1) {
            return $nodes[0];
        }
        // 4. Otherwise, set node to a new DocumentFragment node whose node document is document,
        // and then append each node in nodes, if any, to it.
        $frag = new DocumentFragment();
        $frag->document = $doc;
        foreach ($nodes as $node) {
            $frag->preInsertNodeBeforeChild($node, null);
        }

        return $frag;
    }

    // ==============================================================
    // Helper methods
    // ==============================================================

    protected function getDocument(): ?Document
    {
        return $this->document;
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

    private function hasChildOfType(int $type): bool
    {
        for ($child = $this->first; $child; $child = $child->next) {
            if ($child->nodeType === $type) return true;
        }
        return false;
    }

    private function hasPrecedingSiblingOfType(int $type): bool
    {
        for ($prev = $this->prev; $prev; $prev = $prev->prev) {
            if ($prev->nodeType === $type) return true;
        }
        return false;
    }

    private function hasFollowingSiblingOfType(int $type): bool
    {
        for ($next = $this->next; $next; $next = $next->next) {
            if ($next->nodeType === $type) return true;
        }
        return false;
    }

    private function hasChildOfTypeThatIsNotChild(int $type, Node $child): bool
    {
        for ($node = $this->first; $node; $node = $node->next) {
            if ($node->nodeType === $type && $node !== $child) return true;
        }
        return false;
    }

    /**
     * @return Node[]
     */
    private function collectChildNodes(): array
    {
        $children = [];
        for ($child = $this->first; $child; $child = $child->next) {
            $children[] = $child;
        }
        return $children;
    }
}
