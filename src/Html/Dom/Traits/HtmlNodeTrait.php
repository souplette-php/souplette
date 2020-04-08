<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Traits;

use DOMElement;
use DOMNode;
use JoliPotage\Html\Dom\Api\HtmlNodeInterface;

trait HtmlNodeTrait
{
    public function contains(?DOMNode $other): bool
    {
        if (!$other) {
            return false;
        }

        // TODO: check if comparing w/ getNodePath() is faster in the case $this and $other are in the same document.
        for ($node = $other; $node; $node = $node->parentNode) {
            if ($node === $this) {
                return true;
            }
        }

        return false;
    }

    public function getParentElement(): ?DOMElement
    {
        $parent = $this->parentNode;
        if (!$parent || $parent->nodeType !== HtmlNodeInterface::ELEMENT_NODE) {
            return null;
        }

        return $parent;
    }

    public function getRootNode(): DOMNode
    {
        /** @var DOMNode $this */
        $node = $this;
        while ($node->parentNode) {
            $node = $node->parentNode;
        }

        return $node;
    }
}
