<?php declare(strict_types=1);

namespace Souplette\Dom\Internal;

use Souplette\Dom\Attr;
use Souplette\Dom\Document;
use Souplette\Dom\Node;
use Souplette\Dom\ParentNode;

/**
 * Abstract base class that can be extended by other (potentially non-node) classes,
 * in order manipulate internal properties of other nodes,
 * thus (kinda) emulating the concept of friend classes in C++.
 */
abstract class BaseNode
{
    public readonly int $nodeType;
    public readonly string $nodeName;

    protected ?Document $document = null;
    protected ?ParentNode $parent = null;
    protected ?Node $next = null;
    protected ?Node $prev = null;
    protected ?Node $first = null;
    protected ?Node $last = null;

    protected ?string $value = null;
    /**
     * @var Attr[]
     */
    protected array $attrs = [];

    /**
     * @see https://dom.spec.whatwg.org/#concept-node-adopt
     */
    protected function adopt(Node $node): void
    {
        $doc = $this->nodeType === Node::DOCUMENT_NODE ? $this : $this->document;
        if ($node->document === $doc) {
            return;
        }
        $node->document = $doc;
        if ($node->nodeType === Node::ELEMENT_NODE) {
            foreach ($node->attrs as $attribute) {
                $attribute->document = $doc;
            }
        }
        for ($child = $node->first; $child; $child = $child->next) {
            $this->adopt($child);
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
}
