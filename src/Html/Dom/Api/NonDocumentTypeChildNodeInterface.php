<?php declare(strict_types=1);

namespace JoliPotage\Html\Dom\Api;

use DOMElement;

/**
 * @property-read DOMElement|null $previousElementSibling
 * @property-read DOMElement|null $nextElementSibling
 */
interface NonDocumentTypeChildNodeInterface
{
    const PROPERTIES_READ = [
        'previousElementSibling' => 'getPreviousElementSibling',
        'nextElementSibling' => 'getNextElementSibling',
    ];
    /**
     * Returns the first preceding sibling that is an element, and null otherwise.
     *
     * @return DOMElement|null
     */
    public function getPreviousElementSibling(): ?DOMElement;

    /**
     * Returns the first following sibling that is an element, and null otherwise.
     *
     * @return DOMElement|null
     */
    public function getNextElementSibling(): ?DOMElement;
}
