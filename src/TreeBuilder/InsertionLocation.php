<?php declare(strict_types=1);

namespace ju1ius\HtmlParser\TreeBuilder;

final class InsertionLocation
{
    /**
     * @var \DOMNode
     */
    public $parent;
    /**
     * @var \DOMNode
     */
    public $target;
    /**
     * @var \DOMDocument
     */
    public $document;

    public function __construct(\DOMNode $parent, ?\DOMNode $target = null)
    {
        $this->parent = $parent;
        $this->target = $target ?: $parent->lastChild;
        $this->document = $parent->nodeType === XML_DOCUMENT_NODE ? $parent : $parent->ownerDocument;
    }

    public function insert(\DOMNode $node)
    {
        if (!$this->target) {
            $this->parent->appendChild($node);
        } else {
            $this->parent->insertBefore($node, $this->target->nextSibling);
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
