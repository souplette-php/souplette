<?php declare(strict_types=1);

namespace Souplette\HTML\TreeBuilder;

use Souplette\DOM\Document;
use Souplette\DOM\Element;
use Souplette\DOM\Namespaces;
use Souplette\DOM\Node;
use Souplette\DOM\ParentNode;

final class InsertionLocation
{
    public ?Node $target;
    public ?Document $document;

    public function __construct(
        public ?ParentNode $parent,
        ?Node $target = null,
        public bool $beforeTarget = false
    ) {
        $this->target = $target ?: $parent->_last;
        if ($parent instanceof Document) {
            $this->document = $parent;
        } else {
            $this->document = $parent->_doc;
        }
    }

    public function insert(Node $node): void
    {
        $target = $this->beforeTarget ? $this->target : $this->target?->_next;
        $this->parent->parserInsertBefore($node, $target);
    }

    public function closestAncestor(string $tagName, string $namespaceURI = Namespaces::HTML): ?Element
    {
        $node = $this->parent;
        while ($node) {
            if ($node->localName === $tagName && $node->namespaceURI === $namespaceURI) {
                return $node;
            }
            $node = $node->_parent;
        }
        return null;
    }
}
