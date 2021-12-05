<?php declare(strict_types=1);

namespace Souplette\Dom\Node\Traits;

use Souplette\Dom\Node\Element;
use Souplette\Dom\Node\Node;

/**
 * Used by Element & CharacterData
 */
trait NonDocumentTypeChildNodeTrait
{
    public function getPreviousElementSibling(): ?Element
    {
        for ($node = $this->prev; $node; $node = $node->prev) {
            if ($node->nodeType === Node::ELEMENT_NODE) {
                return $node;
            }
        }
        return null;
    }

    public function getNextElementSibling(): ?Element
    {
        for ($node = $this->next; $node; $node = $node->next) {
            if ($node->nodeType === Node::ELEMENT_NODE) {
                return $node;
            }
        }
        return null;
    }
}
