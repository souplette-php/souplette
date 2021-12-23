<?php declare(strict_types=1);

namespace Souplette\DOM;

use Souplette\DOM\Api\NodeInterface;
use Souplette\DOM\Exception\DOMException;
use Souplette\DOM\Exception\HierarchyRequestError;
use Souplette\DOM\Internal\NodeFlags;
use Souplette\DOM\Traversal\NodeTraversal;
use Souplette\Exception\UndefinedProperty;

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
        return (bool)($this->_flags & NodeFlags::IS_CONNECTED);
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
        if ($this === $other) return 0;
        /** @var ?Attr $attr1 */
        $attr1 = $this->nodeType === Node::ATTRIBUTE_NODE ? $this : null;
        /** @var ?Attr $attr2 */
        $attr2 = $other->nodeType === Node::ATTRIBUTE_NODE ? $other : null;
        $start1 = $attr1 ? $attr1->_parent : $this;
        $start2 = $attr2 ? $attr2->_parent : $other;
        // If either of start1 or start2 is null, then we are disconnected,
        // since one of the nodes is an orphaned attribute node.
        if (!$start1 || !$start2) {
            return (
                self::DOCUMENT_POSITION_DISCONNECTED
                | self::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC
                | self::DOCUMENT_POSITION_PRECEDING
            );
        }
        $chain1 = $chain2 = [];
        if ($attr1) $chain1[] = $attr1;
        if ($attr2) $chain2[] = $attr2;
        if ($attr1 && $attr2 && $start1 === $start2 && $start1) {
            // We are comparing two attributes on the same node.
            // Crawl our attribute map and see which one we hit first.
            $owner1 = $attr1->_parent;
            foreach ($owner1->_attrs as $attr) {
                // If neither of the two determining nodes is a child node
                // and nodeType is the same for both determining nodes,
                // then an implementation-dependent order between the determining nodes is returned.
                // This order is stable as long as no nodes of the same nodeType
                // are inserted into or removed from the direct container.
                // This would be the case, for example, when comparing two attributes of the same element,
                // and inserting or removing additional attributes might change the order between existing attributes.
                if ($attr1->name === $attr->name) {
                    return self::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC | self::DOCUMENT_POSITION_FOLLOWING;
                }
                if ($attr2->name === $attr->name) {
                    return self::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC | self::DOCUMENT_POSITION_PRECEDING;
                }
            }
            return self::DOCUMENT_POSITION_DISCONNECTED;
        }
        // If one node is in the document and the other is not, we must be disconnected.
        // If the nodes have different owning documents, they must be disconnected.
        // Note that we avoid comparing Attr nodes here, since they return false from isConnected() all the time
        // (which seems like a bug but is implemented this way in all major browsers).
        if ($start1->isConnected() !== $start2->isConnected()) {
            return self::DOCUMENT_POSITION_DISCONNECTED
                | self::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC
                | self::DOCUMENT_POSITION_PRECEDING;
        }
        // We need to find a common ancestor container,
        // and then compare the indices of the two immediate children.
        for ($current = $start1; $current; $current = $current->_parent) $chain1[] = $current;
        for ($current = $start2; $current; $current = $current->_parent) $chain2[] = $current;
        $index1 = \count($chain1);
        $index2 = \count($chain2);
        // If the two elements don't have a common root, they're not in the same tree.
        if ($chain1[$index1 - 1] !== $chain2[$index2 - 1]) {
            return self::DOCUMENT_POSITION_DISCONNECTED
                | self::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC
                | self::DOCUMENT_POSITION_PRECEDING;
        }
        // Walk the two chains backwards and look for the first difference.
        $connection = 0;
        //if ($start1->_treeScope !== $start2->_treeScope) $connection = self::DOCUMENT_POSITION_DISCONNECTED | self::DOCUMENT_POSITION_IMPLEMENTATION_SPECIFIC;
        for ($i = min($index1, $index2); $i; $i--) {
            $child1 = $chain1[--$index1];
            $child2 = $chain2[--$index2];
            if ($child1 !== $child2) {
                // If one of the children is an attribute, it wins.
                if ($child1->nodeType === self::ATTRIBUTE_NODE) return self::DOCUMENT_POSITION_FOLLOWING | $connection;
                if ($child2->nodeType === self::ATTRIBUTE_NODE) return self::DOCUMENT_POSITION_PRECEDING | $connection;
                // SKIPPED: If one of the children is a shadow root,
                if (!$child2->_next) return self::DOCUMENT_POSITION_FOLLOWING | $connection;
                if (!$child1->_next) return self::DOCUMENT_POSITION_PRECEDING | $connection;
                // Otherwise, we need to see which node occurs first.
                // Crawl backwards from child2 looking for child1.
                for ($child = $child2->_prev; $child; $child = $child->_prev) {
                    if ($child === $child1) return self::DOCUMENT_POSITION_FOLLOWING | $connection;
                }
                return self::DOCUMENT_POSITION_PRECEDING | $connection;
            }
        }
        // There was no difference between the two parent chains, i.e., one was a subset of the other.
        // The shorter chain is the ancestor.
        return $index1 < $index2 ? (
            self::DOCUMENT_POSITION_FOLLOWING | self::DOCUMENT_POSITION_CONTAINED_BY | $connection
        ) : (
            self::DOCUMENT_POSITION_PRECEDING | self::DOCUMENT_POSITION_CONTAINS | $connection
        );
    }

    /**
     * @see https://dom.spec.whatwg.org/#dom-node-contains
     */
    public function contains(?Node $other): bool
    {
        if (!$other) return false;
        for ($node = $other; $node; $node = $node->_parent) {
            if ($node === $this) return true;
        }
        return false;
    }

    /**
     * The getRootNode(options) method steps are to return:
     * - this’s shadow-including root if options["composed"] is true;
     * - otherwise this’s root.
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
     * @throws DOMException
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
     * @throws DOMException
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
     * @throws DOMException
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

    /**
     * @internal
     */
    public function unlink(): void
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

    protected function locateNamespace(?string $prefix): ?string
    {
        if ($this->_parent?->nodeType === Node::ELEMENT_NODE) {
            return $this->_parent->locateNamespace($prefix);
        }
        return null;
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
