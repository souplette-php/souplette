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
 * @property-read ?ParentNode $parentNode
 * @property-read ?Element $parentElement
 * @property-read ?Node $firstChild
 * @property-read ?Node $lastChild
 * @property-read ?Node $nextSibling
 * @property-read ?Node $previousSibling
 * @property-read Node[] $childNodes
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
    protected ?ParentNode $parent = null;
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
            'childNodes' => $this->getChildNodes(),
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

    abstract public function cloneNode(bool $deep = false): static;

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

    public function hasChildNodes(): bool
    {
        return false;
    }

    public function getChildNodes(): array
    {
        return [];
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

    /**
     * https://dom.spec.whatwg.org/#parent-element
     */
    public function getParentElement(): ?Element
    {
        $parent = $this->parent;
        if ($parent && $parent->nodeType === self::ELEMENT_NODE) {
            return $parent;
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

    protected function adopt(Node $node): void
    {
        $doc = $this->getDocument();
        if ($node->document === $doc) {
            return;
        }
        $node->document = $doc;
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

    // ==============================================================
    // Helper methods
    // ==============================================================

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

    protected function getDocument(): ?Document
    {
        return $this->document;
    }
}
