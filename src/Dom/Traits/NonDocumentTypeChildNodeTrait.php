<?php declare(strict_types=1);

namespace Souplette\Dom\Traits;

use Souplette\Dom\Element;
use Souplette\Dom\Node;

/**
 * Used by Element & CharacterData
 */
trait NonDocumentTypeChildNodeTrait
{
    public function getPreviousElementSibling(): ?Element
    {
        for ($node = $this->_prev; $node; $node = $node->_prev) {
            if ($node->nodeType === Node::ELEMENT_NODE) {
                return $node;
            }
        }
        return null;
    }

    public function getNextElementSibling(): ?Element
    {
        for ($node = $this->_next; $node; $node = $node->_next) {
            if ($node->nodeType === Node::ELEMENT_NODE) {
                return $node;
            }
        }
        return null;
    }
}
