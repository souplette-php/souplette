<?php declare(strict_types=1);

namespace Souplette\Dom;

use Souplette\Dom\Api\NodeInterface;
use Souplette\Dom\Exception\DomException;
use Souplette\Dom\Exception\HierarchyRequestError;
use Souplette\Dom\Exception\UndefinedProperty;
use Souplette\Dom\Internal\NodeFlags;
use Souplette\Dom\Traversal\NodeTraversal;

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

    /** @internal  */
    public int $_flags = 0;
    /** @internal */
    public ?Document $_doc = null;
    /** @internal */
    public ?ParentNode $_treeScope = null;
    /** @internal */
    public ?ParentNode $_parent = null;
    /** @internal */
    public ?Node $_next = null;
    /** @internal */
    public ?Node $_prev = null;
    /** @internal */
    public ?Node $_first = null;
    /** @internal */
    public ?Node $_last = null;
    /** @internal */
    public ?string $_value = null;

    public function __get(string $prop)
    {
        return match ($prop) {
            'baseURI' => '',
            'isConnected' => $this->isConnected(),
            'ownerDocument' => $this->_doc,
            'parentNode' => $this->_parent,
            'parentElement' => $this->getParentElement(),
            'childNodes' => $this->getChildNodes(),
            'firstChild' => $this->_first,
            'lastChild' => $this->_last,
            'previousSibling' => $this->_prev,
            'nextSibling' => $this->_next,
            'nodeValue', 'textContent' => null,
            default => throw UndefinedProperty::forRead($this, $prop),
        };
    }

    public function __set(string $prop, mixed $value)
    {
        match ($prop) {
            'nodeValue', 'textContent' => null,
            default => throw UndefinedProperty::forWrite($this, $prop),
        };
    }

    /**
     * An element is connected if its shadow-including root is a document.
     * @see https://dom.spec.whatwg.org/#connected
     */
    public function isConnected(): bool
    {
        //return $this->getRootNode(['composed' => true])->nodeType === self::DOCUMENT_NODE;
        return $this->hasFlag(NodeFlags::IS_CONNECTED);
    }

    public function getOwnerDocument(): ?Document
    {
        return $this->_doc;
    }

    public function getDocumentNode(): ?Document
    {
        return $this->_doc;
    }

    public function getParentNode(): ?ParentNode
    {
        return $this->_parent;
    }

    /**
     * A node’s parent of type Element is known as its parent element.
     * If the node has a parent of a different type, its parent element is null.
     * @see https://dom.spec.whatwg.org/#parent-element
     */
    public function getParentElement(): ?Element
    {
        $parent = $this->_parent;
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
        return $this->_prev;
    }

    public function getNextSibling(): ?Node
    {
        return $this->_next;
    }

    public function getNodeValue(): ?string
    {
        return null;
    }

    public function setNodeValue(?string $value): void
    {
    }

    public function getTextContent(): ?string
    {
        return null;
    }

    public function setTextContent(?string $value): void
    {
    }

    public function normalize(): void
    {
        $node = $this;
        while ($firstChild = $node->_first) $node = $firstChild;
        while ($node) {
            if ($node === $this) break;
            if ($node->nodeType === self::TEXT_NODE) {
                $node = $this->mergeAdjacentTextNodes($node);
            } else {
                $node = NodeTraversal::nextPostOrder($node);
            }
        }
    }

    public function isSameNode(?Node $otherNode): bool
    {
        return $this === $otherNode;
    }

    public function cloneNode(bool $deep = false): static
    {
        return $this->clone(null, $deep);
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
            $node1 = $attr1->_parent;
        }
        // 5. If node2 is an attribute, then:
        if ($node2->nodeType === self::ATTRIBUTE_NODE) {
            // 1. Set attr2 to node2 and node2 to attr2’s element.
            $attr2 = $node2;
            $node2 = $attr2->_parent;
            // 2. If attr1 and node1 are non-null, and node2 is node1, then:
            if ($attr1 && $node1 && $node2 === $node1) {
                // 1. For each attr in node2’s attribute list:
                foreach ($node2->_attrs as $attr) {
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
        if ($this->hasFlag(NodeFlags::IS_CONNECTED)) {
            return $this->_doc;
        }
        $root = $this;
        while ($root->_parent) $root = $root->_parent;
        return $root;
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-node-lookupprefix
     */
    public function lookupPrefix(?string $namespace): ?string
    {
        if (!$namespace || $this->_parent?->nodeType !== self::ELEMENT_NODE) {
            return null;
        }
        return $this->_parent->locateNamespacePrefix($namespace);
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

    abstract protected function clone(?Document $document, bool $deep = false): static;

    // ==============================================================
    // Node flags
    // ==============================================================

    protected function hasFlag(int $mask): bool
    {
        return $mask === ($this->_flags & $mask);
    }

    protected function setFlag(int $mask): void
    {
        $this->_flags |= $mask;
    }

    protected function clearFlag(int $mask): void
    {
        $this->_flags &= ~$mask;
    }

    // ==============================================================
    // Mutation algorithms
    // ==============================================================

    protected function unlink(): void
    {
        if ($parent = $this->_parent) {
            if ($parent->_first === $this) {
                $parent->_first = $this->_next;
            }
            if ($parent->_last === $this) {
                $parent->_last = $this->_prev;
            }
            $this->_parent = null;
        }
        if ($this->_next) {
            $this->_next->_prev = $this->_prev;
        }
        if ($this->_prev) {
            $this->_prev->_next = $this->_next;
        }
        $this->_next = $this->_prev = null;
    }

    // ==============================================================
    // Mutation notifications
    // ==============================================================

    protected function insertedInto(ParentNode $insertionPoint): void
    {
        if ($insertionPoint->hasFlag(NodeFlags::IS_CONNECTED)) {
            $this->setFlag(NodeFlags::IS_CONNECTED);
        }
    }

    protected function removedFrom(ParentNode $insertionPoint): void
    {
        $this->clearFlag(NodeFlags::IS_CONNECTED);
    }

    // ==============================================================
    // Helper methods
    // ==============================================================

    protected function isInclusiveDescendantOf(?Node $parent): bool
    {
        if (!$parent) return false;
        for ($node = $this; $node; $node = $node->_parent) {
            if ($node === $parent) return true;
        }
        return false;
    }

    protected function isInclusiveAncestorOf(?Node $child): bool
    {
        if (!$child) return false;
        for ($node = $child; $node; $node = $node->_parent) {
            if ($node === $this) return true;
        }
        return false;
    }

    protected function isPrecedingSiblingOf(?Node $sibling): bool
    {
        if (!$sibling) return false;
        for ($node = $sibling; $node; $node = $node->_prev) {
            if ($node === $this) return true;
        }
        return false;
    }

    protected function isFollowingSiblingOf(?Node $sibling): bool
    {
        if (!$sibling) return false;
        for ($node = $sibling; $node; $node = $node->_next) {
            if ($node === $this) return true;
        }
        return false;
    }

    protected function locateNamespace(?string $prefix): ?string
    {
        if (!$this->_parent) return null;
        return $this->_parent->locateNamespace($prefix);
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
            self::COMMENT_NODE => '#comment',
            self::PROCESSING_INSTRUCTION_NODE => '#processing-instruction',
        };
    }

    private function mergeAdjacentTextNodes(Text $node): ?Node
    {
        if (!$node->getLength()) {
            $next = NodeTraversal::nextPostOrder($node);
            $node->remove();
            return $next;
        }
        while ($next = $node->_next) {
            if ($next->nodeType !== self::TEXT_NODE) break;
            /** @var Text $next */
            if (!$next->getLength()) {
                $next->remove();
                continue;
            }
            $node->appendData($next->_value);
            $next->remove();
        }
        return NodeTraversal::nextPostOrder($node);
    }
}
