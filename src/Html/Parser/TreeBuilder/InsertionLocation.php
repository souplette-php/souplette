<?php declare(strict_types=1);

namespace Souplette\Html\Parser\TreeBuilder;

use DOMDocument;
use DOMNode;

final class InsertionLocation
{
    public ?DOMNode $target;
    public DOMDocument $document;

    public function __construct(
        public DOMNode $parent,
        ?DOMNode $target = null,
        public bool $beforeTarget = false
    ) {
        $this->target = $target ?: $parent->lastChild;
        if ($parent instanceof DOMDocument) {
            $this->document = $parent;
        } else {
            $this->document = $parent->ownerDocument;
        }
    }

    public function insert(DOMNode $node)
    {
        if (!$this->target) {
            $this->parent->appendChild($node);
        } else {
            $this->parent->insertBefore($node, $this->beforeTarget ? $this->target : $this->target->nextSibling);
        }
    }

    public function closestAncestor(string $tagName): ?\DOMElement
    {
        $node = $this->target ?: $this->parent;
        while ($node) {
            if ($node->localName === $tagName) {
                return $node;
            }
            $node = $node->parentNode;
        }
        return null;
    }
}
