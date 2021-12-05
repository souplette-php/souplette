<?php declare(strict_types=1);

namespace Souplette\Html\TreeBuilder;

use Souplette\Dom\Document;
use Souplette\Dom\Element;
use Souplette\Dom\Namespaces;
use Souplette\Dom\Node;
use Souplette\Dom\ParentNode;
use Souplette\Dom\Text;

final class InsertionLocation extends Node
{
    public ?Node $target;
    public ?Document $document;

    public function __construct(
        public ?ParentNode $parent,
        ?Node $target = null,
        public bool $beforeTarget = false
    ) {
        $this->target = $target ?: $parent->last;
        if ($parent instanceof Document) {
            $this->document = $parent;
        } else {
            $this->document = $parent->document;
        }
    }

    public function insert(Node $node)
    {
        $target = $this->beforeTarget ? $this->target : $this->target?->next;
        $this->parent->adopt($node);
        $node->unlink();
        if (!$target) {
            $this->parent->uncheckedAppendChild($node);
        } else {
            $this->parent->uncheckedInsertBefore($node, $target);
        }
    }

    public function insertCharacter(string $data)
    {
        $target = $this->target;
        // 4. If there is a Text node immediately before the adjusted insertion location,
        // then append data to that Text node's data.
        if ($target?->nodeType === Node::TEXT_NODE) {
            $target->appendData($data);
        } else if ($this->beforeTarget && $target?->prev?->nodeType === Node::TEXT_NODE) {
            $target->prev->appendData($data);
        } else {
            // Otherwise, create a new Text node whose data is data
            // and whose node document is the same as that of the element in which the adjusted insertion location finds itself,
            // and insert the newly created node at the adjusted insertion location.
            $this->insert(new Text($data));
        }
    }

    public function closestAncestor(string $tagName, string $namespaceURI = Namespaces::HTML): ?Element
    {
        $node = $this->parent;
        while ($node) {
            if ($node->localName === $tagName && $node->namespaceURI === $namespaceURI) {
                return $node;
            }
            $node = $node->parent;
        }
        return null;
    }

    public function isEqualNode(?Node $otherNode): bool
    {
        return true;
    }

    public function cloneNode(bool $deep = false): static
    {
        return new self();
    }
}
