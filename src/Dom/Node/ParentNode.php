<?php declare(strict_types=1);

namespace Souplette\Dom\Node;

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
abstract class ParentNode extends Node
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

    public function getTextContent(): string
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
        $node->document = $this->getDocument();
        $this->replaceAllWithNode($node);
    }

    public function isEqualNode(?Node $otherNode): bool
    {
        if (!$otherNode) return false;
        if ($otherNode === $this) return true;
        if ($otherNode->nodeType !== $this->nodeType) return false;
        return $this->areChildrenEqual($otherNode);
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
}
