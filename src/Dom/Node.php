<?php declare(strict_types=1);

namespace Souplette\Dom;

use Souplette\Dom\Api\NodeInterface;
use Souplette\Dom\Exception\DomException;
use Souplette\Dom\Exception\HierarchyRequestError;
use Souplette\Dom\Exception\NotFoundError;

/**
 * @see https://dom.spec.whatwg.org/#interface-node
 *
 * @property-read int $nodeType
 * @property-read string $nodeName
 *
 * @property-read string $baseURI
 *
 * @property bool $isConnected
 * @property-read ?Document $ownerDocument
 * @property-read ?ParentNode $parentNode
 * @property-read ?Element $parentElement
 * @property-read Node[] $childNodes
 * @property-read ?Node $firstChild
 * @property-read ?Node $lastChild
 * @property-read ?Node $previousSibling
 * @property-read ?Node $nextSibling
 *
 * @property-read ?string $nodeValue
 * @property ?string $textContent
 */
abstract class Node implements NodeInterface
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
    protected ?ParentNode $parent = null;
    protected ?Node $next = null;
    protected ?Node $prev = null;
    protected ?Node $first = null;
    protected ?Node $last = null;

    public function __get(string $prop)
    {
        return match ($prop) {
            'baseURI' => '',
            'isConnected' => $this->isConnected(),
            'ownerDocument' => $this->document,
            'parentNode' => $this->parent,
            'parentElement' => $this->getParentElement(),
            'childNodes' => $this->getChildNodes(),
            'firstChild' => $this->first,
            'lastChild' => $this->last,
            'previousSibling' => $this->prev,
            'nextSibling' => $this->next,
            'nodeValue', 'textContent' => null,
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
            'nodeValue', 'textContent' => null,
            default => throw new \RuntimeException(sprintf(
                'Undefined property %s::$%s',
                $this::class,
                $prop,
            ))
        };
    }

    /**
     * An element is connected if its shadow-including root is a document.
     * @see https://dom.spec.whatwg.org/#connected
     */
    public function isConnected(): bool
    {
        return $this->getRootNode(['composed' => true])->nodeType === self::DOCUMENT_NODE;
    }

    public function getOwnerDocument(): ?Document
    {
        return $this->document;
    }

    public function getParentNode(): ?ParentNode
    {
        return $this->parent;
    }

    /**
     * A node’s parent of type Element is known as its parent element.
     * If the node has a parent of a different type, its parent element is null.
     * @see https://dom.spec.whatwg.org/#parent-element
     */
    public function getParentElement(): ?Element
    {
        $parent = $this->parent;
        if ($parent && $parent->nodeType === self::ELEMENT_NODE) {
            return $parent;
        }
        return null;
    }

    public function hasChildNodes(): bool
    {
        return false;
    }

    public function getChildNodes(): array
    {
        return [];
    }

    public function getFirstChild(): ?Node
    {
        return null;
    }

    public function getLastChild(): ?Node
    {
        return null;
    }

    public function getPreviousSibling(): ?Node
    {
        return $this->prev;
    }

    public function getNextSibling(): ?Node
    {
        return $this->next;
    }

    public function getNodeValue(): ?string
    {
        return null;
    }

    public function setNodeValue(string $value): void
    {
    }

    public function getTextContent(): ?string
    {
        return null;
    }

    public function setTextContent(string $value): void
    {
    }

    public function normalize(): void
    {
        $node = $this;
        while ($firstChild = $node->first) $node = $firstChild;
        while ($node) {
            if ($node === $this) break;
            if ($node->nodeType === self::TEXT_NODE) {
                $node = $this->mergeAdjacentTextNodes($node);
            } else {
                $node = $this->nextPostOrder($node);
            }
        }
    }

    public function isSameNode(?Node $otherNode): bool
    {
        return $this === $otherNode;
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-node-comparedocumentposition
     */
    public function compareDocumentPosition(Node $other): int
    {
        // The compareDocumentPosition(other) method, when invoked, must run these steps:
        // 1.If this is other, then return zero.
        if ($this === $other) return 0;
        // 2. Let node1 be other and node2 be this.
        $node1 = $other;
        $node2 = $this;
        // 3. Let attr1 and attr2 be null.
        $attr1 = $attr2 = null;
        // 4. If node1 is an attribute, then set attr1 to node1 and node1 to attr1’s element.
        if ($node1->nodeType === self::ATTRIBUTE_NODE) {
            $attr1 = $node1;
            $node1 = $attr1->ownerElement;
        }
        // 5. If node2 is an attribute, then:
        if ($node2->nodeType === self::ATTRIBUTE_NODE) {
            // 1. Set attr2 to node2 and node2 to attr2’s element.
            $attr2 = $node2;
            $node2 = $attr2->ownerElement;
            // 2. If attr1 and node1 are non-null, and node2 is node1, then:
            if ($attr1 && $node1 && $node2 === $node1) {
                // 1. For each attr in node2’s attribute list:
                foreach ($node2->attributes as $attr) {
                    // 1. If attr equals attr1, then return the result of adding DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC and DOCUMENT_POSITION_PRECEDING.
                    if ($attr === $attr1) {
                        return self::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC + self::DOCUMENT_POSITION_PRECEDING;
                    }
                    // 2. If attr equals attr2, then return the result of adding DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC and DOCUMENT_POSITION_FOLLOWING.
                    if ($attr === $attr2) {
                        return self::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC + self::DOCUMENT_POSITION_FOLLOWING;
                    }
                }
            }
        }
        // 6. If node1 or node2 is null, or node1’s root is not node2’s root,
        // then return the result of adding DOCUMENT_POSITION_DISCONNECTED, DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC,
        // and either DOCUMENT_POSITION_PRECEDING or DOCUMENT_POSITION_FOLLOWING,
        // with the constraint that this is to be consistent, together.
        if (!$node1 || !$node2 || $node1->getRootNode() !== $node2->getRootNode()) {
            return (
                self::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC
                + self::DOCUMENT_POSITION_DISCONNECTED
                + self::DOCUMENT_POSITION_PRECEDING
            );
        }
        // 7. If node1 is an ancestor of node2 and attr1 is null, or node1 is node2 and attr2 is non-null,
        // then return the result of adding DOCUMENT_POSITION_CONTAINS to DOCUMENT_POSITION_PRECEDING.
        if (
            (!$attr1 && $node1->isInclusiveAncestorOf($node2))
            || ($attr2 && $node1 === $node2)
        ) {
            return self::DOCUMENT_POSITION_CONTAINS + self::DOCUMENT_POSITION_PRECEDING;
        }
        // 8. If node1 is a descendant of node2 and attr2 is null, or node1 is node2 and attr1 is non-null,
        // then return the result of adding DOCUMENT_POSITION_CONTAINED_BY to DOCUMENT_POSITION_FOLLOWING.
        if (
            (!$attr2 && $node1->isInclusiveDescendantOf($node2))
            || ($attr1 && $node1 === $node2)
        ) {
            return self::DOCUMENT_POSITION_CONTAINED_BY + self::DOCUMENT_POSITION_FOLLOWING;
        }
        // 9. If node1 is preceding node2, then return DOCUMENT_POSITION_PRECEDING.
        if ($node1->isPrecedingSiblingOf($node2)) {
            return self::DOCUMENT_POSITION_PRECEDING;
        }
        // 10. Return DOCUMENT_POSITION_FOLLOWING.
        return self::DOCUMENT_POSITION_FOLLOWING;
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-node-contains
     */
    public function contains(?Node $other): bool
    {
        if (!$other) return false;
        return $other->isInclusiveDescendantOf($this);
    }

    /**
     * The getRootNode(options) method steps are to return this’s shadow-including root if options["composed"] is true;
     * otherwise this’s root.
     *
     * https://dom.spec.whatwg.org/#dom-node-getrootnode
     */
    public function getRootNode(array $options = []): Node
    {
        $root = $this;
        while ($root->parent) $root = $root->parent;
        return $root;
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-node-lookupprefix
     */
    public function lookupPrefix(?string $namespace): ?string
    {
        if (!$namespace || $this->parent?->nodeType !== self::ELEMENT_NODE) {
            return null;
        }
        return $this->parent->locateNamespacePrefix($namespace);
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-node-lookupnamespaceuri
     */
    public function lookupNamespaceURI(?string $prefix): ?string
    {
        return $this->locateNamespace($prefix ?: null);
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-node-isdefaultnamespace
     */
    public function isDefaultNamespace(?string $namespace): bool
    {
        $namespace = $namespace ?: null;
        $defaultNamespace = $this->locateNamespace(null);
        return $defaultNamespace === $namespace;
    }

    /**
     * @throws DomException
     */
    public function appendChild(Node $node): Node
    {
        throw new HierarchyRequestError(sprintf(
            'Nodes of type `%s` may not be inserted inside nodes of type `%s`',
            $node->getDebugType(),
            $this->getDebugType(),
        ));
    }

    /**
     * @throws DomException
     */
    public function insertBefore(Node $node, ?Node $child = null): Node
    {
        throw new HierarchyRequestError(sprintf(
            'Nodes of type `%s` may not be inserted inside nodes of type `%s`',
            $node->getDebugType(),
            $this->getDebugType(),
        ));
    }

    /**
     * @throws DomException
     */
    public function removeChild(Node $child): Node
    {
        throw new HierarchyRequestError(sprintf(
            'Nodes of type `%s` do not accept children',
            $this->getDebugType(),
        ));
    }

    /**
     * @throws HierarchyRequestError
     */
    public function replaceChild(Node $node, Node $child): Node
    {
        throw new HierarchyRequestError(sprintf(
            'Nodes of type `%s` may not be inserted inside nodes of type `%s`',
            $node->getDebugType(),
            $this->getDebugType(),
        ));
    }

    // ==============================================================
    // Mutation algorithms
    // ==============================================================

    protected function adopt(Node $node): void
    {
        $doc = $this->getDocumentNode();
        if ($node->document === $doc) {
            return;
        }
        $node->document = $doc;
        for ($child = $node->first; $child; $child = $child->next) {
            $this->adopt($node);
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
     * https://dom.spec.whatwg.org/#concept-node-replace
     *
     * @throws HierarchyRequestError|NotFoundError
     */
    protected function replaceChildWithNode(Node $child, Node $node): Node
    {
        $this->ensureReplacementValidity($child, $node);
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


    // ==============================================================
    // Helper methods
    // ==============================================================

    protected function getDocumentNode(): ?Document
    {
        return $this->document;
    }

    protected function isInclusiveDescendantOf(?Node $parent): bool
    {
        if (!$parent) return false;
        for ($node = $this; $node; $node = $node->parent) {
            if ($node === $parent) return true;
        }
        return false;
    }

    protected function isInclusiveAncestorOf(?Node $child): bool
    {
        if (!$child) return false;
        for ($node = $child; $node; $node = $node->parent) {
            if ($node === $this) return true;
        }
        return false;
    }

    protected function isPrecedingSiblingOf(?Node $sibling): bool
    {
        if (!$sibling) return false;
        for ($node = $sibling; $node; $node = $node->prev) {
            if ($node === $this) return true;
        }
        return false;
    }

    protected function isFollowingSiblingOf(?Node $sibling): bool
    {
        if (!$sibling) return false;
        for ($node = $sibling; $node; $node = $node->next) {
            if ($node === $this) return true;
        }
        return false;
    }

    /**
     * @throws DomException
     */
    protected function convertNodesIntoNode(array $nodes): Node
    {
        $doc = $this->getDocumentNode();
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

    protected function hasPrecedingSiblingOfType(int $type): bool
    {
        for ($prev = $this->prev; $prev; $prev = $prev->prev) {
            if ($prev->nodeType === $type) return true;
        }
        return false;
    }

    protected function hasFollowingSiblingOfType(int $type): bool
    {
        for ($next = $this->next; $next; $next = $next->next) {
            if ($next->nodeType === $type) return true;
        }
        return false;
    }

    protected function locateNamespace(?string $prefix): ?string
    {
        if (!$this->parent) return null;
        return $this->parent->locateNamespace($prefix);
    }

    protected function locateNamespacePrefix(string $namespace): ?string
    {
        return null;
    }

    protected function getDebugType(): string
    {
        return match ($this->nodeType) {
            self::DOCUMENT_NODE => '#document',
            self::DOCUMENT_FRAGMENT_NODE => '#document-fragment',
            self::DOCUMENT_TYPE_NODE => '#doctype',
            self::ELEMENT_NODE => '#element',
            self::ATTRIBUTE_NODE => '#attribute',
            self::TEXT_NODE => '#text',
            self::CDATA_SECTION_NODE => '#cdata-section',
            self::PROCESSING_INSTRUCTION_NODE => '#processing-instruction',
        };
    }

    private function nextPostOrder(Node $current, ?Node $bounds = null): ?Node
    {
        if ($current === $bounds) return null;
        if (!$current->next) return $current->parent;
        $next = $current->next;
        while ($child = $next->first) $next = $child;
        return $next;
    }

    private function mergeAdjacentTextNodes(Text $node): ?Node
    {
        if (!$node->getLength()) {
            $next = $this->nextPostOrder($node);
            $node->remove();
            return $next;
        }
        while ($next = $node->next) {
            if ($next->nodeType !== self::TEXT_NODE) break;
            /** @var Text $next */
            if (!$next->getLength()) {
                $next->remove();
                continue;
            }
            $node->appendData($next->value);
            $next->remove();
        }
        return $this->nextPostOrder($node);
    }
}
