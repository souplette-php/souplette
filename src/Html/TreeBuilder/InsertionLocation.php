<?php declare(strict_types=1);

namespace Souplette\Html\TreeBuilder;

use DOMDocument;
use DOMNode;
use Souplette\Dom\Namespaces;

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

    public function closestAncestor(string $tagName, string $namespaceURI = Namespaces::HTML): ?\DOMElement
    {
        $node = $this->parent;
        while ($node) {
            if ($node->localName === $tagName && $node->namespaceURI === $namespaceURI) {
                return $node;
            }
            $node = $node->parentNode;
        }
        return null;
    }
}
