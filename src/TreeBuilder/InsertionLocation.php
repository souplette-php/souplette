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

    public function __construct(\DOMNode $parent, ?\DOMNode $target = null)
    {
        $this->parent = $parent;
        $this->target = $target ?: $parent->lastChild;
    }

    public function insert(\DOMNode $node)
    {
        if (!$this->target) {
            $this->parent->appendChild($node);
        } else {
            $this->parent->insertBefore($node, $this->target->nextSibling);
        }
    }
}
