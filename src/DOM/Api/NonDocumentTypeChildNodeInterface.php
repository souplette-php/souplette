<?php declare(strict_types=1);

namespace Souplette\DOM\Api;

use Souplette\DOM\Element;

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
