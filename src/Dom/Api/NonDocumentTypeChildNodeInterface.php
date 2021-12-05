<?php declare(strict_types=1);

namespace Souplette\Dom\Api;

use Souplette\Dom\Element;

/**
 * Implemented by Element and CharacterData
 *
 * @property-read ?Element $previousElementSibling
 * @property-read ?Element $nextElementSibling
 */
interface NonDocumentTypeChildNodeInterface extends NodeInterface
{
    public function getPreviousElementSibling(): ?Element;
    public function getNextElementSibling(): ?Element;
}
