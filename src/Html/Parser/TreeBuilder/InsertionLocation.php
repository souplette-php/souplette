<?php declare(strict_types=1);

namespace JoliPotage\Html\Parser\TreeBuilder;

use DOMDocument;
use DOMNode;

final class InsertionLocation
{
    public DOMNode $parent;
    public ?DOMNode $target;
    public bool $beforeTarget = false;
    public DOMDocument $document;

    public function __construct(DOMNode $parent, ?DOMNode $target = null, bool $beforeTarget = false)
    {
        $this->parent = $parent;
        $this->target = $target ?: $parent->lastChild;
        $this->beforeTarget = $beforeTarget;
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
