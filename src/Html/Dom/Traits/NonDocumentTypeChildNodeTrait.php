<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Traits;

use DOMElement;

trait NonDocumentTypeChildNodeTrait
{
    public function getPreviousElementSibling(): ?DOMElement
    {
        $node = $this;
        do {
            $node = $node->previousSibling;
        } while ($node && $node->nodeType !== XML_ELEMENT_NODE);

        return $node;
    }

    public function getNextElementSibling(): ?DOMElement
    {
        $node = $this;
        do {
            $node = $node->nextSibling;
        } while ($node && $node->nodeType !== XML_ELEMENT_NODE);

        return $node;
    }
}
